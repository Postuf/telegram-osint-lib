<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\MessageWithUserId;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\ChatPhoto;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\PhotoInterface;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\UserProfilePhoto;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

/**
 * @see https://core.telegram.org/type/User
 */
class ContactUser extends TLServerMessage implements MessageWithUserId
{
    /** @var string|null */
    private $phone;
    /** @var string|null */
    private $username;

    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'user');
    }

    /**
     * @throws TGException
     *
     * @return UserStatus|null
     */
    public function getStatus(): ?UserStatus
    {
        try {
            $status = $this->getTlMessage()->getNode('status');
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (TGException $e){
            return null;
        }

        return new UserStatus($status);
    }

    /**
     * @throws TGException
     *
     * @return PhotoInterface|null
     */
    public function getPhoto(): ?PhotoInterface
    {
        try {
            $photo = $this->getTlMessage()->getNode('photo');

            if (TLServerMessage::checkType($photo, 'userProfilePhoto')) {
                return new UserProfilePhoto($photo);
            }

            if (TLServerMessage::checkType($photo, 'chatPhoto')) {
                return new ChatPhoto($photo);
            }

            throw new TGException(TGException::ERR_DESERIALIZER_UNKNOWN_OBJECT);
        } catch (TGException $e){
            if($e->getCode() === TGException::ERR_TL_MESSAGE_FIELD_BAD_NODE) {
                return null;
            }

            throw $e;
        }
    }

    public function getUserId(): int
    {
        return (int) $this->getTlMessage()->getValue('id');
    }

    public function getPhone(): ?string
    {
        return $this->phone ?? ($this->getTlMessage()->hasNode('phone')
            ? $this->getTlMessage()->getValue('phone')
            : null);
    }

    /**
     * @see https://core.telegram.org/constructor/user
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username ?? ($this->getTlMessage()->hasNode('username')
            ? $this->getTlMessage()->getValue('username')
            : null);
    }

    public function getAccessHash(): int
    {
        return $this->getTlMessage()->hasNode('access_hash')
            ? $this->getTlMessage()->getValue('access_hash')
            : 0;
    }

    public function getFirstName(): ?string
    {
        return $this->getTlMessage()->hasNode('first_name')
            ? $this->getTlMessage()->getValue('first_name')
            : null;
    }

    public function getLastName(): ?string
    {
        return $this->getTlMessage()->hasNode('last_name')
            ? $this->getTlMessage()->getValue('last_name')
            : null;
    }

    public function getLangCode(): ?string
    {
        return $this->getTlMessage()->getValue('lang_code');
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }
}
