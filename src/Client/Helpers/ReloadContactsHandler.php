<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\Helpers;

use TelegramOSINT\Client\ContactKeepingClient;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\Tools\Phone;

class ReloadContactsHandler
{
    public static function getHandler(ContactKeepingClient $client, array $numbers, array $usernames, callable $onComplete): callable
    {
        return static function (array $contacts) use ($client, $numbers, $usernames, $onComplete) {
            $currentPhones = [];
            $usernames = array_combine($usernames, $usernames);
            foreach ($numbers as $key => $number) {
                $numbers[$key] = Phone::convertToTelegramView($number);
            }
            $numbersCombined = array_combine($numbers, $numbers);

            /** @var ContactUser[] $contacts */
            foreach ($contacts as $contact){
                if ($contact->getPhone()) {
                    $phone = Phone::convertToTelegramView($contact->getPhone());
                    if (isset($numbersCombined[$phone])
                        || !($contact->getUsername() && isset($usernames[$contact->getUsername()]))) {
                        $currentPhones[$phone] = $phone;
                    }
                }
            }

            $existingNumbers = array_intersect($currentPhones, $numbers);
            $obsoleteNumbers = array_diff($currentPhones, $numbers);
            $newNumbers = array_diff($numbers, $currentPhones);

            $addNumbersFunc = static function () use ($client, $newNumbers, $onComplete, $existingNumbers) {
                if (!empty($newNumbers)) {
                    $client->addNumbers(
                        $newNumbers,
                        static function (ImportResult $result) use ($onComplete, $existingNumbers) {
                            $result->importedPhones = array_merge($result->importedPhones, $existingNumbers);
                            $onComplete($result);
                        }
                    );
                } else {
                    $importResult = new ImportResult();
                    $importResult->importedPhones = $existingNumbers;
                    $onComplete($importResult);
                }
            };

            if (!empty($obsoleteNumbers)) {
                $client->delNumbers($obsoleteNumbers, static function () use ($addNumbersFunc) { $addNumbersFunc(); });
            } else {
                $addNumbersFunc();
            }
        };
    }
}
