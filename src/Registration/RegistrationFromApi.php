<?php

namespace Registration;

use Auth\Protocol\ApiAuthorization;
use Client\AuthKey\AuthInfo;
use Client\AuthKey\AuthKey;
use Client\AuthKey\AuthKeyCreator;
use Exception\TGException;
use Logger\Logger;
use MTSerialization\AnonymousMessage;
use SocksProxyAsync\Proxy;
use TGConnection\DataCentre;
use TGConnection\Socket\ProxySocket;
use TGConnection\Socket\TcpSocket;
use TGConnection\SocketMessenger\EncryptedSocketMessenger;
use TGConnection\SocketMessenger\MessageListener;
use TGConnection\SocketMessenger\SocketMessenger;
use TLMessage\TLMessage\ClientMessages\Api\send_sms_code;
use TLMessage\TLMessage\ClientMessages\Shared\sign_in;
use TLMessage\TLMessage\ClientMessages\Shared\sign_up;
use TLMessage\TLMessage\ServerMessages\AuthorizationSelfUser;
use TLMessage\TLMessage\ServerMessages\SentCodeApi;
use Tools\Phone;

class RegistrationFromApi implements RegisterInterface, MessageListener
{
    /**
     * @var AuthKey
     */
    private $blankAuthKey;
    /**
     * @var SocketMessenger
     */
    private $socketMessenger;
    /**
     * @var bool
     */
    private $isPhoneRegistered = false;
    /**
     * @var AccountInfo
     */
    private $accountInfo;
    /**
     * @var Proxy
     */
    private $proxy;
    /**
     * @var string
     */
    private $phone;
    /**
     * @var string
     */
    private $phoneHash;
    /**
     * @var bool
     */
    private $isSmsRequested = false;

    /**
     * @param AccountInfo|null $accountInfo
     * @param Proxy|null       $proxy
     */
    public function __construct(Proxy $proxy = null, AccountInfo $accountInfo = null)
    {
        $this->accountInfo = $accountInfo ? $accountInfo : AccountInfo::generate();
        $this->proxy = $proxy;
    }

    /**
     * @param string   $phoneNumber
     * @param callable $cb          function()
     *
     * @throws TGException
     */
    public function requestCodeForPhone(string $phoneNumber, callable $cb): void
    {
        $this->phone = $phoneNumber;
         $this->requestBlankAuthKey(function (AuthKey $authKey) use ($phoneNumber, $cb) {
             $this->blankAuthKey = $authKey;

             $this->initSocketMessenger();

             $request = new send_sms_code($phoneNumber);
             $this->socketMessenger->getResponseAsync($request, function (AnonymousMessage $smsSentResponse) use ($cb) {
                 $smsSentResponseObj = new SentCodeApi($smsSentResponse);

                 $this->isPhoneRegistered = $smsSentResponseObj->isPhoneRegistered();
                 $this->phoneHash = $smsSentResponseObj->getPhoneCodeHash();
                 $this->isSmsRequested = true;

                 Logger::log('registration', 'Phone registered before: '.($this->isPhoneRegistered ? 'YES' : 'NO'));
                 $cb();
             });
        });
    }

    /**
     * @throws TGException
     */
    private function initSocketMessenger()
    {
        $socket = $this->proxy instanceof Proxy ?
            new ProxySocket($this->proxy, DataCentre::getDefault()) :
            new TcpSocket(DataCentre::getDefault());

        $this->socketMessenger = new EncryptedSocketMessenger($socket, $this->blankAuthKey, $this);
    }

    /**
     * @param callable $onAuthKeyReady function(AuthKey $authKey)
     *
     * @throws TGException
     * @return void
     */
    private function requestBlankAuthKey(callable $onAuthKeyReady)
    {
        $dc = DataCentre::getDefault();
        (new ApiAuthorization($dc))->createAuthKey($onAuthKeyReady);
    }

    /**
     * @param string   $smsCode
     * @param callable $onAuthKeyReady function(AuthKey $authKey)
     *
     * @throws TGException
     */
    public function confirmPhoneWithSmsCode(string $smsCode, callable $onAuthKeyReady): void
    {
        if(!$this->isSmsRequested)
            throw new TGException(TGException::ERR_REG_REQUEST_SMS_CODE_FIRST);
        $callback = function () use ($onAuthKeyReady) {
            $authInfo = (new AuthInfo())
                ->setPhone($this->phone)
                ->setAccountInfo($this->accountInfo);

            $onAuthKeyReady(AuthKeyCreator::attachAuthInfo($this->blankAuthKey, $authInfo));
        };
        $this->isPhoneRegistered
            ? $this->signIn($smsCode, $callback)
            : $this->signUp($callback);
    }

    /**
     * @param string   $smsCode
     * @param callable $cb      function()
     */
    private function signIn(string $smsCode, callable $cb): void
    {
        $signInMessage = new sign_in(
            $this->phone,
            $this->phoneHash,
            trim($smsCode)
        );

        $this->socketMessenger->getResponseAsync($signInMessage, function (AnonymousMessage $response) use ($cb) {
            $authResponse = new AuthorizationSelfUser($response);
            $this->checkSigningResponse($authResponse);
            $cb();
        });
    }

    /**
     * @param callable $cb function()
     */
    private function signUp(callable $cb): void
    {
        $signUpMessage = new sign_up(
            $this->phone,
            $this->phoneHash,
            $this->accountInfo->getFirstName(),
            $this->accountInfo->getLastName()
        );

        $this->socketMessenger->getResponseAsync($signUpMessage, function (AnonymousMessage $message) use ($cb) {
            $response = new AuthorizationSelfUser($message);
            $this->checkSigningResponse($response);
            $cb();
        });
    }

    /**
     * @param AuthorizationSelfUser $response
     *
     * @throws TGException
     */
    private function checkSigningResponse(AuthorizationSelfUser $response): void
    {
        if(!Phone::equal($response->getUser()->getPhone(), $this->phone))
            throw new TGException(TGException::ERR_REG_FAILED);
    }

    /**
     * @param AnonymousMessage $message
     */
    public function onMessage(AnonymousMessage $message)
    {

    }

    public function pollMessages()
    {
        while(true) {
            $this->socketMessenger->readMessage();
        }
    }
}
