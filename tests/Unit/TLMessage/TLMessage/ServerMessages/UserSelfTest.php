<?php

declare(strict_types=1);

namespace Unit\TLMessage\TLMessage\ServerMessages;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\ChatPhoto;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\UserProfilePhoto;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\UserSelf;

class UserSelfTest extends TestCase
{
    private function getObjectWithChatPhoto(): AnonymousMessageMock
    {
        return new AnonymousMessageMock([
            '_'           => 'userSelf',
            'flags'       => 8292,
            'id'          => 1231496859,
            'access_hash' => -3919736352395031712,
            'title'       => "ProfitGate - \u044d\u043a\u043e\u043d\u043e\u043c\u0438\u043a\u0430, \u0442\u0440\u0435\u0439\u0434\u0438\u043d\u0433, \u0438\u043d\u0432\u0435\u0441\u0442\u0438\u0446\u0438\u0438",
            'username'    => 'ProfitGate',
            'photo'       => [
                '_'           => 'chatPhoto',
                'photo_small' => [
                    '_'         => 'fileLocation',
                    'volume_id' => 257020826,
                    'local_id'  => 146876,
                ],
                'photo_big'   => [
                    '_'         => 'fileLocation',
                    'volume_id' => 257020826,
                    'local_id'  => 146878,
                ],
                'dc_id'     => 2,
            ],
            'date'               => 1510407105,
            'version'            => 0,
            'restriction_reason' => null,
            'admin_rights'       => null,
            'banned_rights'      => null,
            'participants_count' => null,
        ]);
    }

    private function getObjectWithUserProfilePhoto(): AnonymousMessageMock
    {
        return new AnonymousMessageMock([
            '_'           => 'userSelf',
            'flags'       => 8292,
            'id'          => 1231496859,
            'access_hash' => -3919736352395031712,
            'title'       => "ProfitGate - \u044d\u043a\u043e\u043d\u043e\u043c\u0438\u043a\u0430, \u0442\u0440\u0435\u0439\u0434\u0438\u043d\u0433, \u0438\u043d\u0432\u0435\u0441\u0442\u0438\u0446\u0438\u0438",
            'username'    => 'ProfitGate',
            'photo'       => [
                '_'           => 'userProfilePhoto',
                'photo_id'    => 3476051023203772362,
                'photo_small' => [
                    '_'         => 'fileLocation',
                    'dc_id'     => 2,
                    'volume_id' => 257020826,
                    'local_id'  => 146876,
                    'secret'    => 4755456613442894998,
                ],
                'photo_big'   => [
                    '_'         => 'fileLocation',
                    'dc_id'     => 2,
                    'volume_id' => 257020826,
                    'local_id'  => 146878,
                    'secret'    => 2054715654679769299,
                ],
            ],
            'date'               => 1510407105,
            'version'            => 0,
            'restriction_reason' => null,
            'admin_rights'       => null,
            'banned_rights'      => null,
            'participants_count' => null,
        ]);
    }

    /**
     * @throws TGException
     */
    public function test_get_photo(): void
    {
        // Check is userProfilePhoto
        $profilePhoto = (new UserSelf($this->getObjectWithUserProfilePhoto()))->getPhoto();
        self::assertInstanceOf(UserProfilePhoto::class, $profilePhoto);
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        self::assertEquals('3476051023203772362', $profilePhoto->getPhotoId());

        // Check is chatPhoto
        $chatPhoto = (new UserSelf($this->getObjectWithChatPhoto()))->getPhoto();
        self::assertInstanceOf(ChatPhoto::class, $chatPhoto);
        self::assertEquals(2, $chatPhoto->getDcId());
    }
}
