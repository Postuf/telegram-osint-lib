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

    /**
     * @param string[]                      $phones
     * @param callable|null                 $cb              function()
     * @param ClientGeneratorInterface|null $clientGenerator
     *
     * @throws TGException
     */
    public function __construct(array $phones, ?callable $cb = null, ClientGeneratorInterface $clientGenerator = null)
    {
        parent::__construct($clientGenerator);
        $this->cb = $cb;
        $this->phones = $phones;
    }

    /**
     * @throws TGException
     */
    protected function getContactsInfo(): void
    {
        $this->login();

        /* info by username */
        $this->infoClient->getInfoByUsername('asen_17', true, true, function ($userInfoModel) {
            if ($userInfoModel->photo)
                file_put_contents(
                    $userInfoModel->username.'.'.$userInfoModel->photo->format,
                    $userInfoModel->photo->bytes
                );
        });

        $this->parseNumbers($this->phones, true, true);
    }

    /**
     * @param string[]      $numbers
     * @param bool          $withPhoto
     * @param bool          $largePhoto
     * @param callable|null $callback   function(UserInfoModel[])
     */
    public function parseNumbers(array $numbers, bool $withPhoto = false, bool $largePhoto = false, ?callable $callback = null): void
    {
        $this->callQueue[] = function () use ($numbers, $withPhoto, $largePhoto, $callback) {
            $counter = count($numbers);
            $models = [];
            $this->infoClient->reloadNumbers($numbers, function (ImportResult $result) use (&$models, $callback, $withPhoto, $largePhoto) {
                $loadFlags = count($result->importedPhones);

                foreach ($result->importedPhones as $importedPhone) {
                    $this->infoClient->getContactByPhone($importedPhone, function (ContactUser $user) use (&$models, &$loadFlags, $callback, $withPhoto, $largePhoto) {
                        $model = new UserInfoModel();
                        $model->id = $user->getUserId();
                        $model->phone = $user->getPhone();
                        $model->langCode = $user->getLangCode();
                        $model->firstName = $user->getFirstName();
                        $model->lastName = $user->getLastName();
                        $model->username = $user->getUsername();

                        $this->infoClient->getFullUserInfo($user, $withPhoto, $largePhoto, function (UserInfoModel $fullModel) use ($model, &$models, $user, &$loadFlags, $callback) {
                            $model->commonChatsCount = $fullModel->commonChatsCount;
                            $model->status = $fullModel->status;
                            $model->bio = $fullModel->bio;

                            $models[$user->getUserId()] = $model;
                            $loadFlags--;

                            if ($loadFlags == 0) {
                                $this->reloadUsersInfo($models, $callback);
                            }
                        });
                    });
                    sleep(2);
                }
            });
        };
    }

    private function reloadUsersInfo(array $models, callable $onComplete)
    {
        $this->infoClient->cleanContacts(function () use (&$models, $onComplete) {
            foreach ($models as $user) {
                if ($user->username) {
                    $this->infoClient->getInfoByUsername($user->username, true, true, function (UserInfoModel $userModel) use (&$models, $onComplete) {
                        $userModel->phone = $models[$userModel->id]->phone;
                        $userModel->bio = $models[$userModel->id]->bio;
                        $userModel->commonChatsCount = $models[$userModel->id]->commonChatsCount;
                        $onComplete($userModel);
                    });
                } else {
                    $user->firstName = '----';
                    $user->lastName = '----';
                    $user->username = '----';
                    $onComplete($user);
                }
                sleep(1);
            }
        });
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->login();
        $this->getContactsInfo();
        foreach ($this->callQueue as $cb) {
            $cb();
        }
        $this->callQueue = [];
        if ($pollAndTerminate) {
            $this->pollAndTerminate();
        }
    }
}
