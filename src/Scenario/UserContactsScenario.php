<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;

class UserContactsScenario extends InfoClientScenario
{
    /** @var callable|null */
    private $cb;
    /** @var callable[] */
    private $callQueue = [];
    /** @var string[] */
    private $phones;
    /** @var bool */
    private $withPhoto;
    /** @var bool */
    private $largePhoto;
    /** @var string[] */
    private $usernames;
    /** @var callable|null */
    private $saveCallback;

    /**
     * @param string[]                      $phones
     * @param array                         $usernames
     * @param callable|null                 $cb              function()
     * @param ClientGeneratorInterface|null $clientGenerator
     * @param bool                          $withPhoto
     * @param bool                          $largePhoto
     * @param callable|null                 $saveCallback    function(string $path, string $bytes)
     *
     * @throws TGException
     */
    public function __construct(
        array $phones,
        array $usernames = [],
        ?callable $cb = null,
        ClientGeneratorInterface $clientGenerator = null,
        bool $withPhoto = true,
        bool $largePhoto = true,
        callable $saveCallback = null
    ) {
        parent::__construct($clientGenerator);
        $this->cb = $cb;
        $this->phones = $phones;
        $this->withPhoto = $withPhoto;
        $this->largePhoto = $largePhoto;
        $this->usernames = $usernames;
        $this->saveCallback = $saveCallback;
    }

    /**
     * @throws TGException
     */
    protected function getContactsInfo(): void
    {
        $this->login(function () {
            $this->parseNumbers($this->phones, $this->withPhoto, $this->largePhoto);

            foreach ($this->usernames as $username) {
                $this->infoClient->getInfoByUsername($username, $this->withPhoto, $this->largePhoto, function (?UserInfoModel $userInfoModel) {
                    if ($userInfoModel && $userInfoModel->photo && $this->saveCallback) {
                        $cb = $this->saveCallback;
                        $cb(
                            $userInfoModel->username.'.'.$userInfoModel->photo->format,
                            $userInfoModel->photo->bytes
                        );
                    }
                });
            }
        });
    }

    /**
     * @param string[] $numbers
     * @param bool     $withPhoto
     * @param bool     $largePhoto
     */
    public function parseNumbers(array $numbers, bool $withPhoto = false, bool $largePhoto = false): void
    {
        if (!$numbers) {
            return;
        }
        $this->callQueue[] = function () use ($numbers, $withPhoto, $largePhoto) {
            $rememberedContacts = [];
            $this->infoClient->reloadContacts($numbers, $this->usernames, function (ImportResult $result) use (
                                                &$rememberedContacts, $withPhoto, $largePhoto) {

                foreach ($result->importedPhones as $importedPhone) {
                    $this->infoClient->getContactByPhone($importedPhone, static function (ContactUser $user) use (&$rememberedContacts) {
                        $rememberedContacts[] = $user;
                    });
                }
                $this->infoClient->cleanContactsBook(function () use ($rememberedContacts, $withPhoto, $largePhoto) {
                    /** @var ContactUser $user */
                    foreach ($rememberedContacts as $user) {
                        $this->infoClient->getFullUserInfo($user, $withPhoto, $largePhoto, function (UserInfoModel $fullModel) use (
                            $user
                        ) {
                            $fullModel->phone = $user->getPhone();
                            if ($fullModel->photo && $this->saveCallback) {
                                $cb = $this->saveCallback;
                                $cb(
                                    ($fullModel->username ?: $fullModel->phone).'.'.$fullModel->photo->format,
                                    $fullModel->photo->bytes
                                );
                            }
                            if ($this->cb) {
                                $callback = $this->cb;
                                $callback($fullModel);
                            }
                        });
                    }
                });
            });
        };
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->authAndPerformActions(function (): void {
            $this->getContactsInfo();
            foreach ($this->callQueue as $cb) {
                $cb();
            }
            $this->callQueue = [];
        }, $pollAndTerminate);
    }
}
