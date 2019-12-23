<?php

namespace Registration;


use Auth\Protocol\ApiAuthorization;
use Client\AuthKey\AuthInfo;
use Client\AuthKey\AuthKey;
use Client\AuthKey\AuthKeyCreator;
use Exception\TGException;
use Logger\Logger;
use MTSerialization\AnonymousMessage;
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
use Tools\Proxy;


class RegistrationFromApi
    implements RegisterInterface, MessageListener
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
     * @var boolean
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
     * @var boolean
     */
    private $isSmsRequested = false;


    /**
     * @param AccountInfo|null $accountInfo
     * @param Proxy|null $proxy
     */
    public function __construct(Proxy $proxy = null, AccountInfo $accountInfo = null)
    {
        $this->accountInfo = $accountInfo ? $accountInfo : AccountInfo::generate();
        $this->proxy = $proxy;
    }


    /**
     * @var $phoneNumber string
     * @throws TGException
     */
    public function requestCodeForPhone(string $phoneNumber): void
    {
        $this->phone = $phoneNumber;
        $this->blankAuthKey = $this->requestBlankAuthKey();
        $this->initSocketMessenger();

        $request = new send_sms_code($phoneNumber);
        $smsSentResponse = $this->socketMessenger->getResponse($request);
        $smsSentResponseObj = new SentCodeApi($smsSentResponse);

        $this->isPhoneRegistered = $smsSentResponseObj->isPhoneRegistered();
        $this->phoneHash = $smsSentResponseObj->getPhoneCodeHash();
        $this->isSmsRequested = true;

        Logger::log('registration', 'Phone registered before: '.($this->isPhoneRegistered ? 'YES' : 'NO'));
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
     * @return AuthKey
     * @throws TGException
     */
    private function requestBlankAuthKey()
    {
        $dc = DataCentre::getDefault();
        return (new ApiAuthorization($dc))->createAuthKey();
    }


    /**
     * @var $smsCode string
     * @return AuthKey
     * @throws TGException
     */
    public function confirmPhoneWithSmsCode(string $smsCode): AuthKey
    {
        if(!$this->isSmsRequested)
            throw new TGException(TGException::ERR_REG_REQUEST_SMS_CODE_FIRST);

        $this->isPhoneRegistered
            ? $this->signIn($smsCode)
            : $this->signUp();

        $authInfo = (new AuthInfo())
            ->setPhone($this->phone)
            ->setAccountInfo($this->accountInfo);

        return AuthKeyCreator::attachAuthInfo($this->blankAuthKey, $authInfo);
    }


    /**
     * @param string $smsCode
     * @throws TGException
     */
    private function signIn(string $smsCode): void
    {
        $signInMessage = new sign_in(
            $this->phone,
            $this->phoneHash,
            trim($smsCode)
        );

        $response = $this->socketMessenger->getResponse($signInMessage);
        $response = new AuthorizationSelfUser($response);
        $this->checkSigningResponse($response);
    }


    /**
     * @throws TGException
     */
    private function signUp(): void
    {
        $signUpMessage = new sign_up(
            $this->phone,
            $this->phoneHash,
            $this->accountInfo->getFirstName(),
            $this->accountInfo->getLastName()
        );

        $response = $this->socketMessenger->getResponse($signUpMessage);
        $response = new AuthorizationSelfUser($response);
        $this->checkSigningResponse($response);
    }


    /**
     * @param AuthorizationSelfUser $response
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
}