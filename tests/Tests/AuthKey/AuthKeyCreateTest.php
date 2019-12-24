<?php


use Auth\Protocol\AppAuthorization;
use Client\AuthKey\AuthKey;
use Client\AuthKey\AuthKeyCreator;
use Client\AuthKey\Versions\AuthKey_v2;
use Client\BasicClient\BasicClientImpl;
use Logger\ClientDebugLogger;
use Logger\Logger;
use MTSerialization\AnonymousMessage;
use PHPUnit\Framework\TestCase;
use TGConnection\DataCentre;
use TGConnection\SocketMessenger\MessageListener;


class AuthKeyCreateTest extends TestCase implements MessageListener
{

    private $session_created = false;


    public function test_generate_auth_key()
    {
        Logger::setupLogger($this->createMock(ClientDebugLogger::class));

        $dc = DataCentre::getDefault();
        /** @noinspection PhpUnhandledExceptionInspection */
        $auth = new AppAuthorization($dc);
        /** @noinspection PhpUnhandledExceptionInspection */
        $auth->createAuthKey(function (AuthKey $key) {
            $serializedKey = $key->getSerializedAuthKey();
            $authKey = AuthKeyCreator::createFromString($serializedKey);

            // check if key in good format
            $this->assertTrue($authKey instanceof AuthKey_v2);

            $client = new BasicClientImpl();
            $client->setMessageListener($this);
            $client->login($key);

            while(!$client->pollMessage()){
                true;
            }

            // check if key login-able
            $this->assertTrue($this->session_created);
        });
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

}
