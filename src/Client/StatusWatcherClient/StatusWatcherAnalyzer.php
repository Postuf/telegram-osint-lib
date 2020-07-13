<?php

namespace TelegramOSINT\Client\StatusWatcherClient;

use TelegramOSINT\Client\Analyzer;
use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\CurrentContacts;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ImportedContacts;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\Updates;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\UpdateShort;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\UpdateUserName;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\UpdateUserPhone;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\UpdateUserStatus;

class StatusWatcherAnalyzer implements Analyzer
{
    /**
     * @var StatusWatcherCallbacksMiddleware
     */
    private $notifier;

    /**
     * @param StatusWatcherCallbacksMiddleware $notifier
     */
    public function __construct(StatusWatcherCallbacksMiddleware $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * @param AnonymousMessage $message
     *
     * @throws TGException
     */
    public function analyzeMessage(AnonymousMessage $message): void
    {
        if(UpdateShort::isIt($message)) {
            $this->onUpdateShort(new UpdateShort($message));
        }

        if(ImportedContacts::isIt($message)){
            $importedContacts = new ImportedContacts($message);
            $this->notifier->onContactsImported($importedContacts);
            $this->analyzeImportedContactsStatus($importedContacts);
        }

        if(Updates::isIt($message)) {
            $this->analyzeUpdates(new Updates($message));
        }

        if(CurrentContacts::isIt($message)) {
            $this->analyzeCurrentStatuses($message);
        }
    }

    /**
     * @param UpdateShort $shortUpdate
     *
     * @throws TGException
     */
    private function onUpdateShort(UpdateShort $shortUpdate): void
    {
        $update = $shortUpdate->getUpdate();

        if(UpdateUserStatus::isIt($update)) {
            $this->onUserStatusChanged(new UpdateUserStatus($update));
        } elseif (UpdateUserName::isIt($update)) {
            $nameUpdate = new UpdateUserName($update);
            $this->notifier->onUserNameChange($nameUpdate->getUserId(), $nameUpdate->getUsername());
        } elseif (UpdateUserPhone::isIt($update)) {
            $phoneUpdate = new UpdateUserPhone($update);
            $this->notifier->onUserPhoneChange($phoneUpdate->getUserId(), $phoneUpdate->getPhone());
        }
    }

    /**
     * @param UpdateUserStatus $newUserStatus
     *
     * @throws TGException
     */
    private function onUserStatusChanged(UpdateUserStatus $newUserStatus): void
    {
        $this->performStatusReaction($newUserStatus->getUserId(), $newUserStatus->getStatus());
    }

    /**
     * @param ImportedContacts $importedContacts
     *
     * @throws TGException
     */
    private function analyzeImportedContactsStatus(ImportedContacts $importedContacts): void
    {
        foreach ($importedContacts->getImportedUsers() as $user) {
            $this->performStatusReaction($user->getUserId(), $user->getStatus(), true);
        }
    }

    /**
     * @param Updates $updates
     *
     * @throws TGException
     */
    private function analyzeUpdates(Updates $updates): void
    {
        foreach ($updates->getUsers() as $user) {
            $this->performStatusReaction($user->getUserId(), $user->getStatus());
        }
        foreach ($updates->getNameUpdates() as $nameUpdate) {
            $this->notifier->onUserNameChange($nameUpdate->getUserId(), $nameUpdate->getUsername());
        }
        foreach ($updates->getPhoneUpdates() as $phoneUpdate) {
            $this->notifier->onUserPhoneChange($phoneUpdate->getUserId(), $phoneUpdate->getPhone());
        }
    }

    /**
     * @param AnonymousMessage $message
     *
     * @throws TGException
     */
    private function analyzeCurrentStatuses(AnonymousMessage $message): void
    {
        $contacts = new CurrentContacts($message);
        $prevContacts = $this->notifier->getCurrentContacts();
        foreach ($contacts->getUsers() as $user) {
            $this->performStatusReaction($user->getUserId(), $user->getStatus(), true);
            if (isset($prevContacts[$user->getUserId()])
                && $prevContacts[$user->getUserId()]->getUsername() !== $user->getUsername()) {
                $this->notifier->onUserNameChange($user->getUserId(), $user->getUsername());
            }
        }
    }

    /**
     * @param int $userId
     * @param UserStatus|null $status
     *
     * @param bool $fromPoll
     * @throws TGException
     */
    private function performStatusReaction(int $userId, $status, bool $fromPoll = false): void
    {
        if(!$status) {
            $this->notifier->onUserHidStatus($userId, new HiddenStatus(HiddenStatus::HIDDEN_SEEN_LONG_AGO));

            return;
        }

        if($status->isHidden()) {
            $this->notifier->onUserHidStatus($userId, $status->getHiddenState());
        }

        if($status->isOnline()) {
            $this->notifier->onUserOnline($userId, $status->getExpires());
        }

        if($status->isOffline()) {
            $this->notifier->onUserOffline($userId, $status->getWasOnline(), $fromPoll);
        }
    }
}
