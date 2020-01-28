<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;

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
            $cbGen = function ($phone) use (&$counter, &$models, $callback) {
                return function (?UserInfoModel $userInfoModel) use (&$counter, $callback, &$models, $phone) {
                    if ($userInfoModel) {
                        $userInfoModel->phone = $phone;
                        if (!$callback) {
                            if ($userInfoModel->photo)
                                file_put_contents(
                                    $userInfoModel->phone.'.'.$userInfoModel->photo->format,
                                    $userInfoModel->photo->bytes
                                );
                            echo "#################################\n";
                            if ($userInfoModel->photo) {
                                $userInfoModel->photo->bytes = 'HIDDEN';
                            }
                            Logger::log(__CLASS__, print_r($userInfoModel, true));
                        } else {
                            $counter--;
                            $models[] = $userInfoModel;
                        }
                    } else {
                        $counter--;
                    }

                    if (!$counter && $callback) {
                        $callback($models);
                        if ($this->cb) {
                            $cb = $this->cb;
                            $cb();
                        }
                    }
                };
            };
            foreach ($numbers as $phone) {
                $this->infoClient->getInfoByPhone($phone, $withPhoto, $largePhoto, $cbGen($phone));
            }
        };
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
