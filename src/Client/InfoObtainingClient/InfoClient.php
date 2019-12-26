<?php


namespace Client\InfoObtainingClient;


use Auth\Protocol\AppAuthorization;
use Client\AuthKey\AuthKey;
use Client\BasicClient\BasicClient;
use Client\BasicClient\BasicClientImpl;
use Client\InfoObtainingClient;
use Client\InfoObtainingClient\Models\PictureModel;
use Client\InfoObtainingClient\Models\UserInfoModel;
use Client\InfoObtainingClient\Models\UserStatusModel;
use Client\StatusWatcherClient\ContactsKeeper;
use Exception\TGException;
use MTSerialization\AnonymousMessage;
use SocksProxyAsync\Proxy;
use TGConnection\DataCentre;
use TGConnection\SocketMessenger\SocketMessenger;
use TLMessage\TLMessage\ClientMessages\Api\get_full_chat;
use TLMessage\TLMessage\ClientMessages\Api\get_all_chats;
use TLMessage\TLMessage\ClientMessages\Shared\export_authorization;
use TLMessage\TLMessage\ClientMessages\Shared\get_config;
use TLMessage\TLMessage\ClientMessages\Shared\get_file;
use TLMessage\TLMessage\ClientMessages\Shared\get_full_user;
use TLMessage\TLMessage\ClientMessages\Shared\import_authorization;
use TLMessage\TLMessage\ClientMessages\Shared\input_file_location;
use TLMessage\TLMessage\ClientMessages\TgApp\contacts_search;
use TLMessage\TLMessage\ClientMessages\TgApp\input_peer_photofilelocation;
use TLMessage\TLMessage\ClientMessages\TgApp\input_peer_user;
use TLMessage\TLMessage\ServerMessages\AuthorizationSelfUser;
use TLMessage\TLMessage\ServerMessages\Contact\ContactFound;
use TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TLMessage\TLMessage\ServerMessages\DcConfigApp;
use TLMessage\TLMessage\ServerMessages\DcOption;
use TLMessage\TLMessage\ServerMessages\ExportedAuthorization;
use TLMessage\TLMessage\ServerMessages\UploadedFile;
use TLMessage\TLMessage\ServerMessages\UserFull;
use TLMessage\TLMessage\TLClientMessage;


class InfoClient implements InfoObtainingClient
{

    private const READ_LIMIT_BYTES = 1024 * 32;  // must be the power of 2 (4096, 8192, 16384 ...)


    /**
     * @var BasicClient
     */
    private $basicClient;
    /**
     * @var BasicClient[]
     */
    private $otherDcClients = [];
    /**
     * @var ContactsKeeper
     */
    private $contactsKeeper;


    public function __construct()
    {
        $this->basicClient = new BasicClientImpl();
        $this->contactsKeeper = new ContactsKeeper($this->basicClient);
    }

    /**
     * @param AuthKey $authKey
     * @param Proxy $proxy
     * @param callable|null $cb
     * @return void
     */
    public function login(AuthKey $authKey, Proxy $proxy = null, ?callable $cb = null)
    {
        $this->basicClient->login($authKey, $proxy, $cb);
    }

    /**
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->basicClient->isLoggedIn();
    }

    /**
     * @return boolean
     *
     * @throws TGException
     */
    public function pollMessage()
    {
        $otherDcMessagePolled = false;
        foreach ($this->otherDcClients as $otherDcClient)
            $otherDcMessagePolled |= $otherDcClient->pollMessage();

        return $this->basicClient->pollMessage() || $otherDcMessagePolled;
    }

    public function getChatMembers(int $id, callable $onComplete) {
        $this->basicClient->getConnection()->getResponseAsync(new get_full_chat($id), $onComplete);
    }

    public function getAllChats(callable $onComplete) {
        $this->basicClient->getConnection()->getResponseAsync(new get_all_chats(), $onComplete);
    }

    /**
     * @param string $phone
     * @param bool $withPhoto
     * @param bool $largePhoto
     * @param callable $onComplete
     * @throws TGException
     */
    public function getInfoByPhone(string $phone, bool $withPhoto, bool $largePhoto, callable $onComplete)
    {
        $this->contactsKeeper->getUserByPhone($phone, function ($user) use ($phone, $withPhoto, $largePhoto, $onComplete) {
            if($user instanceof ContactUser) {
                $this->onContactReady($phone, $withPhoto, $largePhoto, $onComplete);
            } else {
                $this->contactsKeeper->addNumbers([$phone], function () use ($phone, $withPhoto, $largePhoto, $onComplete) {
                    $this->onContactReady($phone, $withPhoto, $largePhoto, $onComplete);
                });
            }
        });
    }

    /**
     * @param string $userName
     * @param bool $withPhoto
     * @param bool $largePhoto
     * @param callable $onComplete
     */
    public function getInfoByUsername(string $userName, bool $withPhoto, bool $largePhoto, callable $onComplete)
    {
        $this->basicClient->getConnection()->getResponseAsync(new contacts_search($userName, 3), function(AnonymousMessage $message) use ($userName, $withPhoto, $largePhoto, $onComplete) {

            $object = new ContactFound($message);

            $onModelBuilt = function (UserInfoModel $model) use($userName, $onComplete) {
                if(strcasecmp(trim($userName), trim($model->username)) == 0)
                    $onComplete($model);
            };

            foreach ($object->getUsers() as $user){
                $this->buildUserInfoModel($user, $withPhoto, $largePhoto, $onModelBuilt);
            }

        });
    }

    /**
     * @param string $phone
     * @param bool $withPhoto
     * @param bool $largePhoto
     * @param callable $onComplete
     * @throws TGException
     */
    private function onContactReady(string $phone, bool $withPhoto, bool $largePhoto, callable $onComplete)
    {
        $this->contactsKeeper->getUserByPhone($phone, function ($user) use ($onComplete, $withPhoto, $largePhoto) {
            if($user instanceof ContactUser){
                $fullUserRequest = new get_full_user($user->getUserId(), $user->getAccessHash());
                $this->basicClient->getConnection()->getResponseAsync($fullUserRequest, function (AnonymousMessage $message) use ($withPhoto, $largePhoto, $onComplete) {
                    $userFull = new UserFull($message);
                    $this->buildUserInfoModel($userFull->getUser(), $withPhoto, $largePhoto, function (UserInfoModel $model) use ($onComplete, $userFull) {
                        $this->extendUserInfoModel($model, $userFull);
                        $onComplete($model);
                    });
                });
            } else {
                $onComplete($user);
            }
        });
    }

    /**
     * @param ContactUser $user
     * @param bool $withPhoto
     * @param bool $largePhoto
     * @param callable $onComplete
     * @throws TGException
     */
    private function buildUserInfoModel(ContactUser $user, bool $withPhoto, bool $largePhoto, callable $onComplete)
    {
        $userModel = new UserInfoModel();
        $userModel->id = $user->getUserId();
        $userModel->phone = $user->getPhone();
        $userModel->username = $user->getUsername();
        $userModel->status = $this->createUserStatusModel($user->getStatus());
        $userModel->accessHash = $user->getAccessHash();

        if($withPhoto){
            $this->createUserPictureModel($user, $largePhoto, function ($photo) use ($userModel, $onComplete) {
                $userModel->photo = $photo;
                $onComplete($userModel);
            });
        } else {
            $onComplete($userModel);
        }
    }

    private function extendUserInfoModel(UserInfoModel $model, UserFull $userFull)
    {
        $model->bio = $userFull->getAbout();
    }

    /**
     * @param ContactUser $user
     * @param bool $largePhotos
     * @param callable $onPictureLoaded
     * @throws TGException
     */
    private function createUserPictureModel(ContactUser $user, bool $largePhotos, callable $onPictureLoaded)
    {
        $profilePhoto = $user->getPhoto();
        if(!$profilePhoto) {
            $onPictureLoaded(null);
            return;
        }

        $photo = $largePhotos ?
            $profilePhoto->getBigPhoto() :
            $profilePhoto->getSmallPhoto();

        $photoLocation = null;
        $dcId = null;

        if($profilePhoto->isV2()){
            $photoLocation = new input_peer_photofilelocation(
                new input_peer_user(
                    $user->getUserId(),
                    $user->getAccessHash()
                ),
                $photo->getVolumeId(),
                $photo->getLocalId(),
                $largePhotos
            );
            $dcId = $profilePhoto->getDcId();
        } else {
            $photoLocation =  new input_file_location(
                $photo->getVolumeId(),
                $photo->getLocalId(),
                $photo->getSecret(),
                $photo->getReference()
            );
            $dcId = $photo->getDcId();
        }

        $this->readPicture($photoLocation, $dcId, $onPictureLoaded);
    }

    /**
     * @param UserStatus|null $userStatus
     * @return UserStatusModel
     */
    private function createUserStatusModel($userStatus)
    {
        if(!$userStatus)
            return null;

        $statusModel = new UserStatusModel();
        $statusModel->is_online = $userStatus->isOnline();
        $statusModel->is_hidden = $userStatus->isHidden();
        $statusModel->was_online = $userStatus->getWasOnline();
        $statusModel->expires = $userStatus->getExpires();

        return $statusModel;
    }

    /**
     * @param TLClientMessage $fileLocation
     * @param int $photoDcId
     * @param callable $onPictureLoaded
     * @throws TGException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    private function readPicture(TLClientMessage $fileLocation, int $photoDcId, callable $onPictureLoaded)
    {
        $isCurrentDc = $photoDcId == $this->basicClient->getConnection()->getDCInfo()->getDcId();
        if($isCurrentDc)
            $this->readPictureFromCurrentDC($this->basicClient->getConnection(), $fileLocation, $onPictureLoaded);
        else
            $this->readPictureFromForeignDC($fileLocation, $photoDcId, $onPictureLoaded);
    }

    /**
     * @param SocketMessenger $basicClient
     * @param TLClientMessage $location
     * @param callable $onPictureLoaded
     * @param PictureModel|null $picModel
     * @param int $offset
     */
    private function readPictureFromCurrentDC(
        SocketMessenger $basicClient, TLClientMessage $location, callable $onPictureLoaded, $picModel = null, int $offset = 0)
    {
        if(!$picModel)
            $picModel = $picModel = new PictureModel();

        $request = new get_file($location, $offset, self::READ_LIMIT_BYTES);
        $basicClient->getResponseAsync($request, function(AnonymousMessage $message) use ($basicClient, $location, $onPictureLoaded, $picModel) {

            $response = new UploadedFile($message);
            if(!$response->isJpeg())
                throw new TGException(TGException::ERR_CLIENT_USER_PIC_UNKNOWN_FORMAT);

            $readBytes = $response->getBytes();
            $readBytesCount = strlen($readBytes);

            $picModel->bytes .= $readBytes;
            $picModel->modificationTime = $response->getModificationTs();
            $picModel->format = $response->getFormatName();

            if($readBytesCount < self::READ_LIMIT_BYTES)
                $onPictureLoaded($picModel);
            else
                $this->readPictureFromCurrentDC($basicClient, $location, $onPictureLoaded, $picModel, strlen($picModel->bytes));
        });
    }


    /**
     * @param TLClientMessage $location
     * @param int $photoDcId
     * @param callable $onPictureLoaded
     */
    private function readPictureFromForeignDC(TLClientMessage $location, int $photoDcId, callable $onPictureLoaded)
    {
        $this->basicClient->getConnection()->getResponseAsync(new get_config(), function (AnonymousMessage $message) use ($location, $photoDcId, $onPictureLoaded) {
            $dcConfigs = new DcConfigApp($message);

            $dcFound = false;
            foreach ($dcConfigs->getDataCenters() as $dc) {
                if ($dc->getId() == $photoDcId && $this->isDcAppropriate($dc)) {

                    $dcFound = true;

                    // create authKey in foreign dc
                    $dc = new DataCentre($dc->getIp(), $dc->getId(), $dc->getPort());
                    $auth = new AppAuthorization($dc);
                    $auth->createAuthKey(function ($authKey) use($onPictureLoaded, $dc, $location) {

                        // login in foreign dc
                        $clientKey = count($this->otherDcClients);
                        $this->otherDcClients[$clientKey] = new BasicClientImpl();
                        $this->otherDcClients[$clientKey]->login($authKey);

                        // export current authorization to foreign dc
                        $exportAuthRequest = new export_authorization($dc->getDcId());
                        $this->basicClient->getConnection()->getResponseAsync($exportAuthRequest, function (AnonymousMessage $message) use ($clientKey, $location, $onPictureLoaded) {
                            $exportedAuthResponse = new ExportedAuthorization($message);

                            // import authorization on foreign dc
                            $importAuthRequest = new import_authorization(
                                $exportedAuthResponse->getUserId(),
                                $exportedAuthResponse->getTransferKey()
                            );
                            $this->otherDcClients[$clientKey]->getConnection()->getResponseAsync($importAuthRequest, function (AnonymousMessage $message) use ($exportedAuthResponse, $clientKey, $location, $onPictureLoaded) {
                                $authorization = new AuthorizationSelfUser($message);
                                if($authorization->getUser()->getUserId() != $exportedAuthResponse->getUserId())
                                    throw new TGException(TGException::ERR_AUTH_EXPORT_FAILED);

                                // make foreign dc current and get the picture
                                $this->readPictureFromCurrentDC($this->otherDcClients[$clientKey]->getConnection(), $location, function ($picture) use ($clientKey, $onPictureLoaded) {
                                    $this->otherDcClients[$clientKey]->terminate();
                                    unset($this->otherDcClients[$clientKey]);
                                    $onPictureLoaded($picture);
                                });

                            });
                        });

                    });

                    break;
                }
            }

            if(!$dcFound)
                throw new TGException(TGException::ERR_CLIENT_PICTURE_ON_UNREACHABLE_DC);

        });
    }

    private function isDcAppropriate(DcOption $dc)
    {
        return preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $dc->getIp());
    }

    /**
     * @return void
     */
    public function terminate()
    {
        $this->basicClient->terminate();
    }

}
