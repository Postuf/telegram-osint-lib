<?php

declare(strict_types=1);

namespace TelegramOSINT\Registration;

use TelegramOSINT\Auth\Protocol\AppAuthorization;
use TelegramOSINT\Client\AuthKey\AuthInfo;
use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TGConnection\Socket\ProxySocket;
use TelegramOSINT\TGConnection\Socket\TcpSocket;
use TelegramOSINT\TGConnection\SocketMessenger\EncryptedSocketMessenger;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;
use TelegramOSINT\TGConnection\SocketMessenger\SocketMessenger;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\Api\get_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared\get_config;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared\get_statuses;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared\sign_in;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared\sign_up;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared\update_status;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_blocked_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_dialogs;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_faved_stickers;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_featured_stickers;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_invite_text;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_langpack;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_languages;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_notify_settings;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_pinned_dialogs;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_state;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_terms_of_service_update;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\get_top_peers;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\init_connection;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\input_notify_chats;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\input_notify_users;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\invoke_with_layer;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\send_sms_code;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\AuthorizationContactUser;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\DcConfigApp;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Languages;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\SentCodeApp;
use TelegramOSINT\Tools\Phone;
use TelegramOSINT\Tools\Proxy;

class RegistrationFromTgApp implements RegisterInterface, MessageListener
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
        $phoneNumber = trim($phoneNumber);

        $this->phone = $phoneNumber;
        $this->requestBlankAuthKey(function (AuthKey $authKey) use ($phoneNumber, $cb) {
            $this->blankAuthKey = $authKey;

            $this->initSocketMessenger();
            $this->initSocketAsOfficialApp(function () use ($phoneNumber, $cb) {
                $request = new send_sms_code($phoneNumber);
                $this->socketMessenger->getResponseAsync($request, function (AnonymousMessage $smsSentResponse) use ($cb) {
                    $smsSentResponseObj = new SentCodeApp($smsSentResponse);

                    if(!$smsSentResponseObj->isSentCodeTypeSms())
                        throw new TGException(TGException::ERR_REG_USER_ALREADY_EXISTS, $smsSentResponse);
                    $this->phoneHash = $smsSentResponseObj->getPhoneCodeHash();
                    $this->isSmsRequested = true;
                    $cb();
                });

            });
        });
    }

    /**
     * pre-actions
     *
     * @param callable $onLastMessageReceived function(AnonymousMessage $message)
     */
    private function initSocketAsOfficialApp(callable $onLastMessageReceived): void
    {
        // config
        $getConfig = new get_config();
        $initConnection = new init_connection($this->accountInfo, $getConfig);
        $invokeWithLayer = new invoke_with_layer(LibConfig::APP_DEFAULT_TL_LAYER_VERSION, $initConnection);

        $this->socketMessenger->getResponseAsync($invokeWithLayer, function (AnonymousMessage $configRequest) use ($onLastMessageReceived) {
            new DcConfigApp($configRequest);

            // possible languages
            $getLanguages = new get_languages();
            $this->socketMessenger->getResponseAsync($getLanguages, function (AnonymousMessage $languages) use ($onLastMessageReceived) {
                $languagesResponse = new Languages($languages);

                if($languagesResponse->getCount() < 5)
                    throw new TGException(TGException::ERR_REG_NOT_OFFICIAL_USER);
                // get language strings
                $getLangPack = new get_langpack($this->accountInfo->getAppLang());
                $this->socketMessenger->getResponseAsync($getLangPack, $onLastMessageReceived);
            });
        });
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
     * @param callable $cb function(AuthKey $authKey)
     *
     * @throws TGException
     */
    private function requestBlankAuthKey(callable $cb): void
    {
        $dc = DataCentre::getDefault();
        (new AppAuthorization($dc))->createAuthKey($cb);
    }

    /**
     * @param string   $smsCode
     * @param callable $onAuthKeyReady function(AuthKey $authKey)
     *
     * @throws TGException
     */
    public function confirmPhoneWithSmsCode(string $smsCode, callable $onAuthKeyReady): void
    {
        $smsCode = trim($smsCode);

        if(!$this->isSmsRequested)
            throw new TGException(TGException::ERR_REG_REQUEST_SMS_CODE_FIRST);
        $this->signInFailed($smsCode, function () use ($onAuthKeyReady) {
            sleep(5);
            $this->signUp(function () use ($onAuthKeyReady) {
                $this->performLoginWorkFlow(function () use ($onAuthKeyReady) {
                    $this->socketMessenger->terminate();

                    $authInfo = (new AuthInfo())
                        ->setPhone($this->phone)
                        ->setAccountInfo($this->accountInfo);

                    $onAuthKeyReady(AuthKeyCreator::attachAuthInfo($this->blankAuthKey, $authInfo));
                });
            });
        });
    }

    /**
     * post-actions
     *
     * @param callable $cb function(AnonymousMessage $message)
     */
    private function performLoginWorkFlow(callable $cb): void
    {
        $this->socketMessenger->getResponseConsecutive([
            new get_config(),
            new update_status(true),
            new get_terms_of_service_update(),
            new get_notify_settings(new input_notify_chats()),
            new get_notify_settings(new input_notify_users()),
            new get_invite_text(),
            new get_pinned_dialogs(),
            new get_state(),
            new get_blocked_contacts(),
            new get_contacts(),
            new get_dialogs(),
            new get_faved_stickers(),
            new get_featured_stickers(),
            new get_top_peers(),
            new get_statuses(),
        ], $cb);
    }

    /**
     * @param string   $smsCode
     * @param callable $onMessageReceived function(AnonymousMessage $message)
     */
    private function signInFailed(string $smsCode, callable $onMessageReceived): void
    {
        $signInMessage = new sign_in(
            $this->phone,
            $this->phoneHash,
            trim($smsCode)
        );

        $this->socketMessenger->getResponseAsync($signInMessage, $onMessageReceived);
        //return
        //    RpcError::isIt($response) &&
        //    (new RpcError($response))->isPhoneNumberUnoccupied();
    }

    /**
     * @param callable $onUserAuthorized function(AuthorizationContactUser $user)
     */
    private function signUp(callable $onUserAuthorized): void
    {
        $signUpMessage = new sign_up(
            $this->phone,
            $this->phoneHash,
            $this->accountInfo->getFirstName(),
            $this->accountInfo->getLastName()
        );

        $this->socketMessenger->getResponseAsync($signUpMessage, function (AnonymousMessage $response) use ($onUserAuthorized) {
            $authResponse = new AuthorizationContactUser($response);
            $this->checkSigningResponse($authResponse);
            $onUserAuthorized($authResponse);
        });
    }

    /**
     * @param AuthorizationContactUser $response
     *
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

    public function pollMessages()
    {
        while(true) {
            $this->socketMessenger->readMessage();
        }
    }
}
