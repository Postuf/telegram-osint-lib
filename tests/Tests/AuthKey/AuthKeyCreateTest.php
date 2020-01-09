<?php

declare(strict_types=1);

use Auth\Protocol\AppAuthorization;
use Client\AuthKey\AuthKey;
use Client\AuthKey\AuthKeyCreator;
use Client\AuthKey\Versions\AuthKey_v2;
use Client\BasicClient\BasicClientImpl;
use Exception\TGException;
use Logger\ClientDebugLogger;
use Logger\Logger;
use MTSerialization\AnonymousMessage;
use PHPUnit\Framework\TestCase;
use TGConnection\DataCentre;
use TGConnection\SocketMessenger\MessageListener;

class AuthKeyCreateTest extends TestCase implements MessageListener
{
    /** @var bool */
    private $session_created = false;

    /**
     * Test that telegram auth key is formatted correctly.
     *
     * @throws TGException
     */
    public function test_generate_auth_key(): void
    {
        Logger::setupLogger($this->createMock(ClientDebugLogger::class));

        $dc = DataCentre::getDefault();
        // perform several retries in case of failure
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->performAuth($dc);
                break;
            } catch (TGException $e) {
                // skip exception
            }

            sleep(1000);
        }
    }

    /**
     * @param AnonymousMessage $message
     */
    public function onMessage(AnonymousMessage $message)
    {
        if($message->getType() == 'msg_container' && $message->getNodes('messages')[0]->getType() == 'new_session_created')
            $this->session_created = true;

        if($message->getType() == 'new_session_created')
            $this->session_created = true;
    }

    /**
     * @param DataCentre $dc
     *
     * @throws TGException
     */
    protected function performAuth(DataCentre $dc): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $auth = new AppAuthorization($dc);
        /* @noinspection PhpUnhandledExceptionInspection */
        $auth->createAuthKey(function (AuthKey $key) {
            $serializedKey = $key->getSerializedAuthKey();
            $authKey = AuthKeyCreator::createFromString($serializedKey);

            // check if key in good format
            $this->assertTrue($authKey instanceof AuthKey_v2);

            $client = new BasicClientImpl();
            $client->setMessageListener($this);
            $client->login($key);

            while (!$client->pollMessage()) {
                true;
            }

            // check if key login-able
            $this->assertTrue($this->session_created);
        });
    }
}
