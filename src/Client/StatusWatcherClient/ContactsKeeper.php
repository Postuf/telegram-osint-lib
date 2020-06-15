<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\StatusWatcherClient;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\add_contact;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\contacts_search;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\delete_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\import_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\reset_saved_contacts;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactFound;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\CurrentContacts;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ImportedContacts;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\Updates;
use TelegramOSINT\Tools\Phone;
use TelegramOSINT\Tools\Username;
use TelegramOSINT\Validators\ImportedPhoneValidator;

class ContactsKeeper
{
    /**
     * Official client does next: contact list splits up to 500-long bunches and sends
     * This will lead to retrying most of the contacts in future (reversed from client).
     */
    private static $CONTACTS_IMPORT_PORTION = 15;
    /**
     * Frequent requests on getting full contact list can be considered by TG server
     * as FLOOD. In order to prevent this, there is an artificial limitation left.
     */
    private static $FLOOD_FREQUENCY_LIMIT_SEC = 3;

    /**
     * @var BasicClient
     */
    private $client;
    /**
     * @var ContactUser[]
     */
    private $contacts = [];
    /**
     * @var ContactUser[]
     */
    private $contactsByPhone = [];
    /**
     * @var int
     */
    private $lastDelContactsTime = 0;
    /**
     * @var bool
     */
    private $contactsLoaded = false;
    /**
     * @var bool
     */
    private $contactsLoading = false;
    /**
     * @var callable[]
     */
    private $contactsLoadedQueue = [];

    /**
     * @param BasicClient   $client
     * @param ContactUser[] $startContacts
     */
    public function __construct(BasicClient $client, array $startContacts = [])
    {
        $this->client = $client;
        $this->contacts = $startContacts;
    }

    /**
     * @param string[] $numbers
     * @param callable $onComplete function(ImportResult $result)
     *
     * @throws TGException
     */
    public function addNumbers(array $numbers, callable $onComplete)
    {
        $validator = new ImportedPhoneValidator();
        foreach ($numbers as $number) {
            if(!$validator->validate($number))
                throw new TGException(TGException::ERR_CLIENT_BAD_NUMBER_FORMAT, 'Number: '.$number);
        }

        $this->getUsersByPhones($numbers, function (array $contacts) use ($onComplete, $numbers) {

            if(!empty($contacts)) {
                $phones = []; foreach ($contacts as $contact) $phones[] = $contact->getPhone();

                throw new TGException(TGException::ERR_CLIENT_ADD_PHONE_ALREADY_IN_ADDRESS_BOOK, implode(',', $phones));
            }

            $iterations = ceil(count($numbers) / self::$CONTACTS_IMPORT_PORTION);
            $importResult = new ImportResult();
            $responseCounter = 0;

            for($i = 0; $i < $iterations; $i++){

                $localNumbers = array_slice($numbers, $i * self::$CONTACTS_IMPORT_PORTION, self::$CONTACTS_IMPORT_PORTION);
                $request = new import_contacts($localNumbers);

                $callback = function (AnonymousMessage $message) use ($request, $onComplete, $importResult, &$responseCounter, $iterations) {
                    try{
                        $this->onImported($message, $request, $importResult);
                    } finally {
                        if (++$responseCounter == $iterations)
                            $onComplete($importResult);
                    }
                };

                $this->client->getConnection()->getResponseAsync($request, $callback);
            }

        });
    }

    /**
     * @param string   $userName
     * @param callable $onComplete function(bool)
     */
    public function addUser(string $userName, callable $onComplete)
    {
        $this->client->getConnection()->getResponseAsync(
            new contacts_search($userName, 1),
            function (AnonymousMessage $message) use ($userName, $onComplete) {
                $object = new ContactFound($message);
                $users = $object->getUsers();
                if(empty($users)){
                    $onComplete(false);

                    return;
                }

                $user = $users[0];
                $id = $user->getUserId();
                $hash = $user->getAccessHash();
                $username = $user->getUsername();
                if(!Username::equal($userName, $username)){
                    $onComplete(false);

                    return;
                }

                $this->getUserById($id, function ($contact) use ($id, $hash, $username, $onComplete) {

                    if($contact)
                        throw new TGException(TGException::ERR_CLIENT_ADD_USERNAME_ALREADY_IN_ADDRESS_BOOK, $username);
                    $this->client->getConnection()->getResponseAsync(
                        new add_contact($id, $hash),
                        function (AnonymousMessage $message) use ($onComplete) {
                            $updates = new Updates($message);
                            $users = $updates->getUsers();
                            $this->onContactsAdded($users);
                            $onComplete(true);
                        }
                    );
                });
            }
        );
    }

    /**
     * @param string   $userName
     * @param callable $onComplete function()
     *
     * @throws TGException
     */
    public function delUser(string $userName, callable $onComplete)
    {
        $this->getUserByName($userName, function ($contact) use ($userName, $onComplete) {
            if($contact instanceof ContactUser)
                $this->delContacts([$contact], $onComplete);
        });
    }

    /**
     * @param AnonymousMessage $message
     * @param import_contacts  $request
     * @param ImportResult     $importResult
     *
     * @throws TGException
     */
    private function onImported(AnonymousMessage $message, import_contacts $request, ImportResult $importResult)
    {
        $importedUsers = new ImportedContacts($message);
        $this->updateImportedPhones($importedUsers, $importResult);
        $this->onContactsAdded($importedUsers->getImportedUsers());
        $this->checkReplacedContacts($request, $importedUsers, $importResult);
        // checks
        $this->checkLimitsExceeded($importedUsers);
    }

    /**
     * @param ImportedContacts $imported
     * @param ImportResult     $importResult
     *
     * @throws TGException
     */
    private function updateImportedPhones(ImportedContacts $imported, ImportResult $importResult)
    {
        foreach ($imported->getImportedUsers() as $importedUser)
            $importResult->importedPhones[] = $importedUser->getPhone();
    }

    /**
     * @param import_contacts  $source
     * @param ImportedContacts $results
     * @param ImportResult     $importResult
     *
     * @throws TGException
     */
    private function checkReplacedContacts(import_contacts $source, ImportedContacts $results, ImportResult $importResult)
    {
        $userMap = [];
        foreach ($results->getImportedUsers() as $user)
            $userMap[$user->getUserId()] = $user->getPhone();

        foreach ($results->getImportedClients() as $client){
            $expectedPhone = $source->getPhoneByClientId($client->getClientId());
            $actualPhone = isset($userMap[$client->getUserId()]) ? $userMap[$client->getUserId()] : false;
            if($expectedPhone !== false && $actualPhone !== false)
                if((int) $expectedPhone != (int) $actualPhone)
                    $importResult->replacedPhones[] = $actualPhone;
        }
    }

    /**
     * @param ImportedContacts $results
     *
     * @throws TGException
     */
    private function checkLimitsExceeded(ImportedContacts $results)
    {
        $retryCount = count($results->getRetryContacts());
        if($retryCount > 0) {
            throw new TGException(TGException::ERR_MSG_IMPORT_CONTACTS_LIMIT_EXCEEDED, 'Count: '.$retryCount);
        }
    }

    /**
     * @param array    $numbers
     * @param callable $onComplete function()
     */
    public function delNumbers(array $numbers, callable $onComplete)
    {
        $this->getUsersByPhones($numbers, function (array $contacts) use ($numbers, $onComplete) {
            // if all current contacts to be deleted
            if(count($contacts) === count($this->contacts)) {
                $this->cleanContacts($onComplete);
            } else {
                $this->delContacts($contacts, $onComplete);
            }
        });
    }

    /**
     * @return ContactUser[]
     */
    public function getContacts(): array
    {
        return $this->contacts;
    }

    /**
     * @param ContactUser[] $contacts
     * @param callable      $onComplete function()
     *
     * @throws TGException
     */
    private function delContacts(array $contacts, callable $onComplete)
    {
        if(time() - $this->lastDelContactsTime < self::$FLOOD_FREQUENCY_LIMIT_SEC)
            throw new TGException(TGException::ERR_CLIENT_FLOODING_ACTIONS, 'delete_contacts too frequent');
        $this->lastDelContactsTime = time();

        // prepare deletion
        $deleteContactsRequest = new delete_contacts();
        foreach ($contacts as $contact)
            $deleteContactsRequest->addToDelete($contact->getAccessHash(), $contact->getUserId());

        // delete
        $this->client->getConnection()->getResponseAsync($deleteContactsRequest, function (AnonymousMessage $message) use ($onComplete, $contacts) {

            $updates = new Updates($message);
            if(count($updates->getUsers()) != count($contacts))
                throw new TGException(TGException::ERR_CLIENT_COULD_NOT_DELETE);
            $this->onContactsDeleted($contacts);
            $onComplete();
        });
    }

    /**
     * @param callable $onComplete function()
     */
    public function cleanContacts(callable $onComplete)
    {
        if(!$this->contactsLoaded(function () use ($onComplete) {$this->cleanContacts($onComplete); }))
            return;

        // reset contacts
        /** @noinspection NullPointerExceptionInspection */
        $this->client->getConnection()->getResponseAsync(
            new reset_saved_contacts(),
            function (/* @noinspection PhpUnusedParameterInspection */ AnonymousMessage $message) use ($onComplete) {
                $this->delContacts($this->contacts, $onComplete);
            }
        );
    }

    /**
     * @param ContactUser[] $contacts
     */
    private function onContactsDeleted(array $contacts)
    {
        foreach ($contacts as $contact) {
            unset($this->contacts[$contact->getUserId()]);
            unset($this->contactsByPhone[Phone::convertToTelegramView($contact->getPhone())]);
        }
    }

    /**
     * @param ContactUser[] $contacts
     */
    private function onContactsAdded(array $contacts)
    {
        foreach ($contacts as $contact) {
            $this->contacts[$contact->getUserId()] = $contact;
            $this->contactsByPhone[Phone::convertToTelegramView($contact->getPhone())] = $contact;
        }
    }

    /**
     * @param callable $onLoadedCallback function()
     *
     * @return bool
     */
    protected function contactsLoaded(callable $onLoadedCallback): bool
    {
        if (!$this->contactsLoaded) {
            if ($this->contactsLoading) {
                $this->contactsLoadedQueue[] = $onLoadedCallback;
            } else {
                $this->reloadCurrentContacts($onLoadedCallback);
            }
        }

        return $this->contactsLoaded;
    }

    /**
     * @param callable $onReloaded function(ContactUser[] $users)
     */
    public function reloadCurrentContacts(callable $onReloaded)
    {
        $this->contactsLoadedQueue[] = $onReloaded;
        $this->contactsLoading = true;

        $conn = $this->client->getConnection();
        if ($conn) {
            $conn->getResponseAsync(new get_contacts(), function (AnonymousMessage $message) {
                $users = new CurrentContacts($message);
                $this->onContactsAdded($users->getUsers());
                $this->contactsLoading = false;
                $this->contactsLoaded = true;
                $this->callOnContactsLoadedCallbacks();
            });
        }
    }

    private function callOnContactsLoadedCallbacks()
    {
        if($this->contactsLoaded){

            $errors = [];

            foreach ($this->contactsLoadedQueue as $pendingCallback) {
                try {$pendingCallback($this->contacts); } /* @noinspection PhpRedundantCatchClauseInspection */ catch (TGException $e){$errors[] = $e; }
            }

            $this->contactsLoadedQueue = [];

            if(!empty($errors))
                throw $errors[0];
        }
    }

    /**
     * @param int      $userId
     * @param callable $onSuccess function(ContactUser $user)
     */
    public function getUserById(int $userId, callable $onSuccess)
    {
        if(!$this->contactsLoaded(function () use ($userId, $onSuccess) {$this->getUserById($userId, $onSuccess); }))
            return;

        $onSuccess(isset($this->contacts[$userId]) ?
            $this->contacts[$userId] :
            null);
    }

    /**
     * @param string   $phone
     * @param callable $onSuccess function(ContactUser $user)
     */
    public function getUserByPhone(string $phone, callable $onSuccess)
    {
        if(!$this->contactsLoaded(function () use ($phone, $onSuccess) {$this->getUserByPhone($phone, $onSuccess); }))
            return;

        $this->getUsersByPhones([$phone], function ($users) use ($onSuccess) {
            $onSuccess(empty($users) ? null : $users[0]);
        });
    }

    /**
     * @param string   $username
     * @param callable $onSuccess function(ContactUser $user)
     *
     * @throws TGException
     */
    private function getUserByName(string $username, callable $onSuccess)
    {
        if(!$this->contactsLoaded(function () use ($username, $onSuccess) {$this->getUserByName($username, $onSuccess); }))
            return;

        foreach ($this->contacts as $contact) {
            if(Username::equal($contact->getUsername(), $username)) {
                $onSuccess($contact);
                break;
            }
        }
    }

    /**
     * @param array    $phones
     * @param callable $onSuccess function(ContactUser[] $users)
     */
    private function getUsersByPhones(array $phones, callable $onSuccess)
    {
        if(!$this->contactsLoaded(function () use ($phones, $onSuccess) {$this->getUsersByPhones($phones, $onSuccess); }))
            return;

        $contacts = [];
        foreach ($phones as $phone) {
            $phoneFormatted = Phone::convertToTelegramView($phone);
            if (isset($this->contactsByPhone[$phoneFormatted])) {
                $contacts[] = $this->contactsByPhone[$phoneFormatted];
            }
        }

        $onSuccess($contacts);
    }

    /**
     * @param callable $onSuccess function(ContactUser[] $users)
     */
    public function getCurrentContacts(callable $onSuccess)
    {
        if(!$this->contactsLoaded(function () use ($onSuccess) {$this->getCurrentContacts($onSuccess); }))
            return;

        $onSuccess($this->contacts);
    }
}
