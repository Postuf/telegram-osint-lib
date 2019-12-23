<?php
declare(strict_types=1);

namespace Registration;


use Auth\Protocol\AppAuthorization;
use Client\AuthKey\AuthInfo;
use Client\AuthKey\AuthKey;
use Client\AuthKey\AuthKeyCreator;
use Exception\TGException;
use LibConfig;
use MTSerialization\AnonymousMessage;
use TGConnection\DataCentre;
use TGConnection\Socket\ProxySocket;
use TGConnection\Socket\TcpSocket;
use TGConnection\SocketMessenger\EncryptedSocketMessenger;
use TGConnection\SocketMessenger\MessageListener;
use TGConnection\SocketMessenger\SocketMessenger;
use TLMessage\TLMessage\ClientMessages\Api\get_contacts;
use TLMessage\TLMessage\ClientMessages\Shared\get_config;
use TLMessage\TLMessage\ClientMessages\Shared\get_statuses;
use TLMessage\TLMessage\ClientMessages\Shared\sign_in;
use TLMessage\TLMessage\ClientMessages\Shared\sign_up;
use TLMessage\TLMessage\ClientMessages\Shared\update_status;
use TLMessage\TLMessage\ClientMessages\TgApp\get_blocked_contacts;
use TLMessage\TLMessage\ClientMessages\TgApp\get_dialogs;
use TLMessage\TLMessage\ClientMessages\TgApp\get_faved_stickers;
use TLMessage\TLMessage\ClientMessages\TgApp\get_featured_stickers;
use TLMessage\TLMessage\ClientMessages\TgApp\get_invite_text;
use TLMessage\TLMessage\ClientMessages\TgApp\get_langpack;
use TLMessage\TLMessage\ClientMessages\TgApp\get_languages;
use TLMessage\TLMessage\ClientMessages\TgApp\get_notify_settings;
use TLMessage\TLMessage\ClientMessages\TgApp\get_pinned_dialogs;
use TLMessage\TLMessage\ClientMessages\TgApp\get_state;
use TLMessage\TLMessage\ClientMessages\TgApp\get_terms_of_service_update;
use TLMessage\TLMessage\ClientMessages\TgApp\get_top_peers;
use TLMessage\TLMessage\ClientMessages\TgApp\init_connection;
use TLMessage\TLMessage\ClientMessages\TgApp\input_notify_chats;
use TLMessage\TLMessage\ClientMessages\TgApp\input_notify_users;
use TLMessage\TLMessage\ClientMessages\TgApp\invoke_with_layer;
use TLMessage\TLMessage\ClientMessages\TgApp\send_sms_code;
use TLMessage\TLMessage\ServerMessages\AuthorizationContactUser;
use TLMessage\TLMessage\ServerMessages\DcConfigApp;
use TLMessage\TLMessage\ServerMessages\Languages;
use TLMessage\TLMessage\ServerMessages\Rpc\RpcError;
use TLMessage\TLMessage\ServerMessages\SentCodeApp;
use Tools\Phone;
use Tools\Proxy;


class RegistrationFromTgApp
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
        $phoneNumber = trim($phoneNumber);

        $this->phone = $phoneNumber;
        $this->blankAuthKey = $this->requestBlankAuthKey();

        $this->initSocketMessenger();
        $this->initSocketAsOfficialApp();

        $request = new send_sms_code($phoneNumber);
        $smsSentResponse = $this->socketMessenger->getResponse($request);
        $smsSentResponseObj = new SentCodeApp($smsSentResponse);

        if(!$smsSentResponseObj->isSentCodeTypeSms())
            throw new TGException(TGException::ERR_REG_USER_ALREADY_EXISTS, $smsSentResponse);

        $this->phoneHash = $smsSentResponseObj->getPhoneCodeHash();
        $this->isSmsRequested = true;
    }


    /**
     * pre-actions
     * @throws TGException
     */
    private function initSocketAsOfficialApp(): void
    {
        // config
        $getConfig = new get_config();
        $initConnection = new init_connection($this->accountInfo, $getConfig);
        $invokeWithLayer = new invoke_with_layer(LibConfig::APP_DEFAULT_TL_LAYER_VERSION, $initConnection);

        $configRequest = $this->socketMessenger->getResponse($invokeWithLayer);
        new DcConfigApp($configRequest);

        // possible languages
        $getLanguages = new get_languages();
        $languages = $this->socketMessenger->getResponse($getLanguages);
        $languagesResponse = new Languages($languages);

        if($languagesResponse->getCount() < 5)
            throw new TGException(TGException::ERR_REG_NOT_OFFICIAL_USER);

        // get language strings
        $getLangPack = new get_langpack($this->accountInfo->getAppLang());
        $this->socketMessenger->getResponse($getLangPack);
    }


    /**
     * @throws TGException
     */
    private function initSocketMessenger(): void
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
    private function requestBlankAuthKey(): AuthKey
    {
        $dc = DataCentre::getDefault();
        return (new AppAuthorization($dc))->createAuthKey();
    }


    /**
     * @var $smsCode string
     * @return AuthKey
     * @throws TGException
     */
    public function confirmPhoneWithSmsCode(string $smsCode): AuthKey
    {
        $smsCode = trim($smsCode);

        if(!$this->isSmsRequested)
            throw new TGException(TGException::ERR_REG_REQUEST_SMS_CODE_FIRST);

        //if(!$this->signInFailed($smsCode))
        //    throw new TGException(TGException::ERR_REG_USER_ALREADY_EXISTS);

        $this->signInFailed($smsCode);
        sleep(5);
        $this->signUp();
        $this->performLoginWorkFlow();
        $this->socketMessenger->terminate();

        $authInfo = (new AuthInfo())
            ->setPhone($this->phone)
            ->setAccountInfo($this->accountInfo);

        return AuthKeyCreator::attachAuthInfo($this->blankAuthKey, $authInfo);
    }


    /**
     * post-actions
     */
    private function performLoginWorkFlow(): void
    {
        $this->socketMessenger->getResponse(new get_config());
        $this->socketMessenger->getResponse(new update_status(true));
        //$this->socketMessenger->getResponse(new get_languages());
        //$this->socketMessenger->getResponse(new get_langpack($this->accountInfo->getAppLang()));
        $this->socketMessenger->getResponse(new get_terms_of_service_update());

        $this->socketMessenger->getResponse(new get_notify_settings(new input_notify_chats()));
        $this->socketMessenger->getResponse(new get_notify_settings(new input_notify_users()));
        $this->socketMessenger->getResponse(new get_invite_text());
        $this->socketMessenger->getResponse(new get_pinned_dialogs());
        //$this->socketMessenger->getResponse(new get_languages());
        //$this->socketMessenger->getResponse(new register_device_push_java());
        $this->socketMessenger->getResponse(new get_state());
        $this->socketMessenger->getResponse(new get_blocked_contacts());
        $this->socketMessenger->getResponse(new get_contacts());
        $this->socketMessenger->getResponse(new get_dialogs());
        $this->socketMessenger->getResponse(new get_faved_stickers());
        $this->socketMessenger->getResponse(new get_featured_stickers());
        $this->socketMessenger->getResponse(new get_top_peers());
        $this->socketMessenger->getResponse(new get_statuses());
        //$this->socketMessenger->getResponse(new register_device_push());
    }


    /**
     * @param string $smsCode
     * @return bool
     * @throws TGException
     */
    private function signInFailed(string $smsCode): bool
    {
        $signInMessage = new sign_in(
            $this->phone,
            $this->phoneHash,
            trim($smsCode)
        );

        $response = $this->socketMessenger->getResponse($signInMessage);
        return
            RpcError::isIt($response) &&
            (new RpcError($response))->isPhoneNumberUnoccupied();
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
        $response = new AuthorizationContactUser($response);
        $this->checkSigningResponse($response);
    }


    /**
     * @param AuthorizationContactUser $response
     * @throws TGException
     */
    private function checkSigningResponse(AuthorizationContactUser $response): void
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