<?php

namespace Tests\Tests\TLMessage\TLMessage\ServerMessages;

use Exception\TGException;
use PHPUnit\Framework\TestCase;
use TLMessage\TLMessage\ServerMessages\Contact\ContactUser;

class ContactUserTest extends TestCase
{
    private function getObjectOnLine()
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return new AnonymousMessageMock([
            '_'           => 'user',
            'id'          => 438562352,
            'access_hash' => 2811936216873835544,
            'first_name'  => 'name_89169904863',
            'last_name'   => 'l_f4d6bed238',
            'username'    => 'AseN_17',
            'phone'       => 79169904855,
            'photo'       => [
                '_'           => 'userProfilePhoto',
                'photo_id'    => 806194743786710955,
                'photo_small' => [
                    '_'         => 'fileLocation',
                    'dc_id'     => 2,
                    'volume_id' => 225517222,
                    'local_id'  => 141372,
                    'secret'    => 4952891847968332097,
                ],

                'photo_big' => [
                    '_'         => 'fileLocation',
                    'dc_id'     => 2,
                    'volume_id' => 225517222,
                    'local_id'  => 141374,
                    'secret'    => -5785720690880313215,
                ],

            ],

            'status' => [
                '_'       => 'userStatusOnline',
                'expires' => 1533377307,
            ],
        ]);
    }

    private function getObjectOffLine()
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return new AnonymousMessageMock([
            '_'           => 'user',
            'id'          => 438562352,
            'access_hash' => 2811936216873835544,
            'first_name'  => 'name_89169904863',
            'last_name'   => 'l_f4d6bed238',
            'username'    => 'AseN_17',
            'phone'       => 79169904855,
            'photo'       => [
                '_'           => 'userProfilePhoto',
                'photo_id'    => 806194743786710955,
                'photo_small' => [
                    '_'         => 'fileLocation',
                    'dc_id'     => 2,
                    'volume_id' => 225517222,
                    'local_id'  => 141372,
                    'secret'    => 4952891847968332097,
                ],

                'photo_big' => [
                    '_'         => 'fileLocation',
                    'dc_id'     => 2,
                    'volume_id' => 225517222,
                    'local_id'  => 141374,
                    'secret'    => -5785720690880313215,
                ],

            ],

            'status' => [
                '_'          => 'userStatusOffline',
                'was_online' => 1533377309,
            ],
        ]);
    }

    private function getObjectHidden()
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return new AnonymousMessageMock([
            '_'           => 'user',
            'id'          => 438562352,
            'access_hash' => 2811936216873835544,
            'first_name'  => 'name_89169904863',
            'last_name'   => 'l_f4d6bed238',
            'username'    => 'AseN_17',
            'phone'       => 79169904855,
            'photo'       => [
                '_'           => 'userProfilePhoto',
                'photo_id'    => 806194743786710955,
                'photo_small' => [
                    '_'         => 'fileLocation',
                    'dc_id'     => 2,
                    'volume_id' => 225517222,
                    'local_id'  => 141372,
                    'secret'    => 4952891847968332097,
                ],

                'photo_big' => [
                    '_'         => 'fileLocation',
                    'dc_id'     => 2,
                    'volume_id' => 225517222,
                    'local_id'  => 141374,
                    'secret'    => -5785720690880313215,
                ],

            ],

            'status' => [
                '_' => 'userStatusEmpty',
            ],
        ]);
    }

    /**
     * @throws TGException
     */
    public function test_correct_field_mapping()
    {
        $asAnonymous = $this->getObjectOnLine();
        $userContact = new ContactUser($asAnonymous);

        $this->assertEquals($userContact->getUsername(), 'AseN_17');
        $this->assertEquals($userContact->getPhone(), 79169904855);
        $this->assertEquals($userContact->getUserId(), 438562352);
        $this->assertEquals($userContact->getAccessHash(), 2811936216873835544);

    }

    public function test_user_online()
    {
        $asAnonymous = $this->getObjectOnLine();
        /** @noinspection PhpUnhandledExceptionInspection */
        $userContact = new ContactUser($asAnonymous);

        $this->assertTrue($userContact->getStatus()->isOnline());
        $this->assertFalse($userContact->getStatus()->isOffline());
        $this->assertFalse($userContact->getStatus()->isHidden());
        $this->assertEquals($userContact->getStatus()->getExpires(), 1533377307);

    }

    public function test_user_offline()
    {
        $asAnonymous = $this->getObjectOffLine();
        /** @noinspection PhpUnhandledExceptionInspection */
        $userContact = new ContactUser($asAnonymous);

        $this->assertFalse($userContact->getStatus()->isOnline());
        $this->assertTrue($userContact->getStatus()->isOffline());
        $this->assertFalse($userContact->getStatus()->isHidden());
        $this->assertEquals($userContact->getStatus()->getWasOnline(), 1533377309);

    }

    public function test_user_empty()
    {
        $asAnonymous = $this->getObjectHidden();
        /** @noinspection PhpUnhandledExceptionInspection */
        $userContact = new ContactUser($asAnonymous);

        $this->assertFalse($userContact->getStatus()->isOnline());
        $this->assertFalse($userContact->getStatus()->isOffline());
        $this->assertTrue($userContact->getStatus()->isHidden());

    }
}
