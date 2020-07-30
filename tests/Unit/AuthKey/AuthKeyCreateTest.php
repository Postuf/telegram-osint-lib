<?php

declare(strict_types=1);

namespace Unit\AuthKey;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Auth\Protocol\AppAuthorization;
use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\AuthKey\Versions\AuthKey_v2;
use TelegramOSINT\Client\BasicClient\BasicClientImpl;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;

class AuthKeyCreateTest extends TestCase implements MessageListener
{
    /** @var bool */
    private $sessionCreated = false;

    /**
     * Test that telegram auth key is formatted correctly.
     */
    public function test_generate_auth_key(): void
    {
        $dc = DataCentre::getDefault();
        // perform several retries in case of failure
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->performAuth($dc);
                break;
            } catch (TGException $e) {
                //
            }

            sleep(1);
        }
    }

    /**
     * @param AnonymousMessage $message
     */
    public function onMessage(AnonymousMessage $message): void
    {
        if ($message->getType() === 'msg_container') {
            $message = $message->getNodes('messages')[0];
        }

        if ($message->getType() === 'new_session_created') {
            $this->sessionCreated = true;
        }
    }

    /**
     * @param DataCentre $dc
     *
     * @throws TGException
     */
    protected function performAuth(DataCentre $dc): void
    {
        $auth = new AppAuthorization($dc);
        $gotSession = false;
        $timeStart = time();
        $auth->createAuthKey(function (AuthKey $key) use (&$gotSession, $timeStart) {
            $serializedKey = $key->getSerializedAuthKey();
            $authKey = AuthKeyCreator::createFromString($serializedKey);

            // check if key in good format
            $this->assertInstanceOf(AuthKey_v2::class, $authKey);

            $client = new BasicClientImpl();
            $client->setMessageListener($this);
            $client->login($key, null, static function () use (&$gotSession) {
                $gotSession = true;
            });
            while (!$this->sessionCreated && time() < $timeStart + 5) {
                $client->pollMessage();
                usleep(100000);
            }
            $this->assertTrue($this->sessionCreated);
        });
        while (!$gotSession && time() < $timeStart + 5) {
            $auth->poll();
            usleep(10000);
        }
    }
}
