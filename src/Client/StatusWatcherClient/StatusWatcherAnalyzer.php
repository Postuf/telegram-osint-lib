<?php

namespace Client\StatusWatcherClient;

use Client\Analyzer;
use Client\StatusWatcherClient\Models\HiddenStatus;
use Exception\TGException;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\ServerMessages\Contact\CurrentContacts;
use TLMessage\TLMessage\ServerMessages\Contact\ImportedContacts;
use TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TLMessage\TLMessage\ServerMessages\Update\Updates;
use TLMessage\TLMessage\ServerMessages\Update\UpdateShort;
use TLMessage\TLMessage\ServerMessages\Update\UpdateUserStatus;

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
    public function analyzeMessage(AnonymousMessage $message)
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

        if(CurrentContacts::isIt($message))
            $this->analyzeCurrentStatuses($message);
    }

    /**
     * @param UpdateShort $shortUpdate
     *
     * @throws TGException
     */
    private function onUpdateShort(UpdateShort $shortUpdate)
    {
        $update = $shortUpdate->getUpdate();

        if(UpdateUserStatus::isIt($update))
            $this->onUserStatusChanged(new UpdateUserStatus($update));
    }

    /**
     * @param UpdateUserStatus $newUserStatus
     *
     * @throws TGException
     */
    private function onUserStatusChanged(UpdateUserStatus $newUserStatus)
    {
        $this->performStatusReaction($newUserStatus->getUserId(), $newUserStatus->getStatus());
    }

    /**
     * @param ImportedContacts $importedContacts
     *
     * @throws TGException
     */
    private function analyzeImportedContactsStatus(ImportedContacts $importedContacts)
    {
        foreach ($importedContacts->getImportedUsers() as $user)
            $this->performStatusReaction($user->getUserId(), $user->getStatus());
    }

    /**
     * @param Updates $updates
     *
     * @throws TGException
     */
    private function analyzeUpdates(Updates $updates)
    {
        foreach ($updates->getUsers() as $user)
            $this->performStatusReaction($user->getUserId(), $user->getStatus());
    }

    /**
     * @param AnonymousMessage $message
     *
     * @throws TGException
     */
    private function analyzeCurrentStatuses(AnonymousMessage $message)
    {
        $contacts = new CurrentContacts($message);
        foreach ($contacts->getUsers() as $user)
            $this->performStatusReaction($user->getUserId(), $user->getStatus());
    }

    /**
     * @param int             $userId
     * @param UserStatus|null $status
     *
     * @throws TGException
     */
    private function performStatusReaction(int $userId, $status)
    {
        if(!$status) {
            $this->notifier->onUserHidStatus($userId, new HiddenStatus(HiddenStatus::HIDDEN_SEEN_LONG_AGO));

            return;
        }

        if($status->isHidden())
            $this->notifier->onUserHidStatus($userId, $status->getHiddenState());

        if($status->isOnline())
            $this->notifier->onUserOnline($userId, $status->getExpires());

        if($status->isOffline())
            $this->notifier->onUserOffline($userId, $status->getWasOnline());
    }
}
