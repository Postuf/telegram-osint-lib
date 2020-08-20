<?php

/** @noinspection NullPointerExceptionInspection */

namespace TelegramOSINT\Client\InfoObtainingClient;

use Closure;
use TelegramOSINT\Auth\Authorization;
use TelegramOSINT\Auth\Protocol\AppAuthorization;
use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\CachingClient;
use TelegramOSINT\Client\ChannelClient;
use TelegramOSINT\Client\ContactKeepingClientImpl;
use TelegramOSINT\Client\InfoObtainingClient;
use TelegramOSINT\Client\InfoObtainingClient\Models\FileModel;
use TelegramOSINT\Client\InfoObtainingClient\Models\GroupId;
use TelegramOSINT\Client\InfoObtainingClient\Models\PictureModel;
use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Client\InfoObtainingClient\Models\UserStatusModel;
use TelegramOSINT\Client\UserInfoClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\Scenario\BasicClientGeneratorInterface;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TGConnection\SocketMessenger\SocketMessenger;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\contacts_get_located;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\contacts_resolve_username;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\contacts_search;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\export_authorization;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_all_chats;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_common_chats;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_config;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_file;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_full_channel;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_full_chat;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_full_user;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_history;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_participants;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\import_authorization;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\input_channel;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\input_file_location;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\input_peer_photofilelocation;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\input_peer_user;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\input_photofilelocation;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\join_channel;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\messages_search;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\AuthorizationSelfUser;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactsFound;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\DcConfigApp;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\ExportedAuthorization;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\UploadedFile;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\UserFull;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\UserProfilePhoto;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;
use TelegramOSINT\Tools\BanInvalidator;
use TelegramOSINT\Tools\Cache;
use TelegramOSINT\Tools\CacheFactoryInterface;
use TelegramOSINT\Tools\CacheInvalidator;
use TelegramOSINT\Tools\DiskCacheFactory;
use TelegramOSINT\Tools\Proxy;
use TelegramOSINT\Tools\Username;

class InfoClient extends ContactKeepingClientImpl implements InfoObtainingClient, CachingClient, ChannelClient, UserInfoClient
{
    private const READ_LIMIT_BYTES = 1024 * 256;  // must be the power of 2 (4096, 8192, 16384 ...)

    /**
     * @var BasicClient[]
     */
    private array $otherDcClients = [];
    /**
     * @var Authorization[]
     */
    private array $notEncryptedClients = [];
    /** @var BasicClientGeneratorInterface */
    private BasicClientGeneratorInterface $generator;
    /** @var Proxy|null */
    private ?Proxy $proxy;
    /** @var CacheFactoryInterface */
    private $authKeyCacheFactory;
    /** @var Cache|null */
    private ?Cache $authKeyCache;
    /** @var CacheInvalidator */
    private $authKeyCacheInvalidator;
    /** @var AuthKey|null */
    private ?AuthKey $authKey;

    public function __construct(
        BasicClientGeneratorInterface $generator,
        ?CacheFactoryInterface $authKeyCacheFactory = null,
        ?CacheInvalidator $invalidator = null
    ) {
        $this->generator = $generator;
        $this->basicClient = $generator->generate();
        $this->authKeyCacheFactory = $authKeyCacheFactory ?? new DiskCacheFactory();
        $this->authKeyCacheInvalidator = $invalidator ?? new BanInvalidator();
        parent::__construct(null, $this->basicClient);
    }

    /**
     * @param AuthKey       $authKey
     * @param Proxy|null    $proxy
     * @param callable|null $cb      function()
     *
     * @return void
     */
    public function login(AuthKey $authKey, ?Proxy $proxy, callable $cb): void
    {
        $this->proxy = $proxy;
        $this->authKey = $authKey;
        $this->authKeyCache = $this->authKeyCacheFactory->generate($authKey);
        $this->basicClient->login($authKey, $proxy, $cb);
    }

    public function isLoggedIn(): bool
    {
        return $this->basicClient->isLoggedIn();
    }

    /**
     * @throws TGException
     *
     * @return bool
     */
    public function pollMessage(): bool
    {
        try {
            $otherDcMessagePolled = false;
            foreach ($this->otherDcClients as $otherDcClient) {
                $otherDcMessagePolled |= $otherDcClient->pollMessage();
            }
            foreach ($this->notEncryptedClients as $client) {
                $client->poll();
            }

            $this->processDeferredQueue();

            return $this->basicClient->pollMessage() || $otherDcMessagePolled;
        } catch (TGException $e) {
            if ($this->authKeyCache) {
                $this->authKeyCacheInvalidator->invalidateIfNeeded($e, $this->authKeyCache);
            }

            throw $e;
        }
    }

    public function getChatMembers(int $id, callable $onComplete): void
    {
        $this->basicClient->getConnection()->getResponseAsync(new get_full_chat($id), $onComplete);
    }

    public function getChannelMembers(GroupId $id, callable $onComplete): void
    {
        $this->basicClient->getConnection()->getResponseAsync(
            new get_full_channel($id->getId(), $id->getAccessHash()),
            $onComplete
        );
    }

    /**
     * @noinspection PhpUnused
     * @noinspection UnknownInspectionInspection
     *
     * @param GroupId  $id
     * @param callable $onComplete
     */
    public function getFullChannel(GroupId $id, callable $onComplete): void
    {
        $this->basicClient->getConnection()->getResponseAsync(
            new get_full_channel($id->getId(), $id->getAccessHash()),
            $onComplete
        );
    }

    public function getChatMessages(int $id, int $limit, ?int $since, ?int $lastId, callable $onComplete): void
    {
        $request = new get_history($id, $limit, (int) $since, (int) $lastId);
        $this->basicClient->getConnection()->getResponseAsync(
            $request,
            $onComplete
        );
    }

    public function getChannelMessages(GroupId $id, int $limit, ?int $since, ?int $lastId, callable $onComplete): void
    {
        $request = new get_history($id->getId(), $limit, (int) $since, (int) $lastId, $id->getAccessHash());
        $this->basicClient->getConnection()->getResponseAsync(
            $request,
            $onComplete
        );
    }

    public function getChannelLinks(GroupId $id, int $limit, ?int $since, ?int $lastId, callable $onComplete): void
    {
        $request = new messages_search($id->getId(), $limit, $id->getAccessHash(), (int) $since, (int) $lastId);
        $this->basicClient->getConnection()->getResponseAsync(
            $request,
            $onComplete
        );
    }

    public function getCommonChats(GroupId $id, int $limit, int $max_id, callable $onComplete): void
    {
        $this->basicClient->getConnection()->getResponseAsync(
            new get_common_chats($id->getId(), $id->getAccessHash(), $limit, $max_id),
            $onComplete
        );
    }

    public function getParticipants(GroupId $id, int $offset, callable $onComplete): void
    {
        $channel = new input_channel($id->getId(), $id->getAccessHash());
        $this->basicClient->getConnection()->getResponseAsync(new get_participants($channel, $offset), $onComplete);
    }

    public function getParticipantsSearch(GroupId $id, string $username, callable $onComplete): void
    {
        $channel = new input_channel($id->getId(), $id->getAccessHash());
        $this->basicClient->getConnection()->getResponseAsync(new get_participants($channel, 0, $username), $onComplete);
    }

    /**
     * @param float    $latitude
     * @param float    $longitude
     * @param callable $onComplete function(AnonymousMessage $msg)
     */
    public function getLocated(float $latitude, float $longitude, callable $onComplete): void
    {
        $request = new contacts_get_located($latitude, $longitude);
        $this->basicClient->getConnection()->getResponseAsync($request, $onComplete);
    }

    /**
     * @param GroupId  $id
     * @param int      $msgId
     * @param int      $userId
     * @param callable $onComplete function(?UserInfoModel $model)
     * @noinspection PhpUnused
     * @noinspection UnknownInspectionInspection
     */
    public function getFullUser(GroupId $id, int $msgId, int $userId, callable $onComplete): void
    {
        $request = new get_full_user($id->getId(), $id->getAccessHash(), $msgId, $userId);
        $cbUnpacker = static function (AnonymousMessage $msg) use ($onComplete) {
            if (UserFull::isIt($msg)) {
                $onComplete(null);

                return;
            }
            $user = new UserInfoModel();
            $user->id = $msg->getValue('id');
            $user->username = $msg->getValue('username');
            $onComplete($user);
        };
        $this->basicClient->getConnection()->getResponseAsync($request, $cbUnpacker);
    }

    /**
     * @param string   $username
     * @param callable $onComplete function(AnonymousMessage $msg)
     */
    public function resolveUsername(string $username, callable $onComplete): void
    {
        $this->basicClient->getConnection()->getResponseAsync(new contacts_resolve_username($username), $onComplete);
    }

    /**
     * @param GroupId  $id
     * @param callable $onComplete
     */
    public function joinChannel(GroupId $id, callable $onComplete): void
    {
        $this->basicClient->getConnection()->getResponseAsync(
            new join_channel($id->getId(), $id->getAccessHash()),
            $onComplete
        );
    }

    /**
     * @param callable $onComplete function(AnonymousMessage $msg)
     */
    public function getAllChats(callable $onComplete): void
    {
        $this->basicClient->getConnection()->getResponseAsync(new get_all_chats(), $onComplete);
    }

    /**
     * @param string   $phone
     * @param bool     $withPhoto
     * @param bool     $largePhoto
     * @param callable $onComplete function(?UserInfoModel $model)
     */
    public function getInfoByPhone(string $phone, bool $withPhoto, bool $largePhoto, callable $onComplete): void
    {
        $this->contactsKeeper->getUserByPhone($phone, function ($user) use ($phone, $withPhoto, $largePhoto, $onComplete) {
            if ($user instanceof ContactUser) {
                $this->onContactReady($phone, $withPhoto, $largePhoto, $onComplete);
            } else {
                $this->contactsKeeper->addNumbers([$phone], function () use ($phone, $withPhoto, $largePhoto, $onComplete) {
                    $this->onContactReady($phone, $withPhoto, $largePhoto, $onComplete);
                });
            }
        });
    }

    /**
     * @param string   $userName
     * @param bool     $withPhoto
     * @param bool     $largePhoto
     * @param callable $onComplete function(?UserInfoModel $model)
     */
    public function getInfoByUsername(string $userName, bool $withPhoto, bool $largePhoto, callable $onComplete): void
    {
        $this->basicClient->getConnection()->getResponseAsync(
            new contacts_search($userName, 3),
            function (AnonymousMessage $message) use ($userName, $withPhoto, $largePhoto, $onComplete) {
                $object = new ContactsFound($message);

                $found = false;
                foreach ($object->getUsers() as $user) {
                    if (!$user->getBot() && Username::equal($userName, $user->getUsername())) {
                        $this->buildUserInfoModel($user, $withPhoto, $largePhoto, $onComplete);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $onComplete(null);
                }
            }
        );
    }

    /**
     * @param ContactUser $user
     * @param bool        $withPhoto
     * @param bool        $largePhoto
     * @param callable    $onComplete
     */
    public function getFullUserInfo(ContactUser $user, bool $withPhoto, bool $largePhoto, callable $onComplete): void
    {
        $fullUserRequest = new get_full_user($user->getUserId(), $user->getAccessHash());
        $this->basicClient->getConnection()->getResponseAsync($fullUserRequest, function (AnonymousMessage $message) use ($withPhoto, $largePhoto, $onComplete) {
            $userFull = new UserFull($message);
            $this->buildUserInfoModel($userFull->getUser(), $withPhoto, $largePhoto, function (UserInfoModel $model) use ($onComplete, $userFull) {
                /** @noinspection UnusedFunctionResultInspection */
                $this->extendUserInfoModel($model, $userFull);
                $onComplete($model);
            });
        });
    }

    /**
     * @param string   $phone
     * @param bool     $withPhoto
     * @param bool     $largePhoto
     * @param callable $onComplete function(?UserInfoModel $model)
     */
    private function onContactReady(string $phone, bool $withPhoto, bool $largePhoto, callable $onComplete): void
    {
        $this->contactsKeeper->getUserByPhone($phone, function ($user) use ($onComplete, $withPhoto, $largePhoto, $phone) {
            if ($user instanceof ContactUser) {
                $username = $user->getUsername();
                if (!empty($username)) {
                    $this->contactsKeeper->delNumbers([$phone], function () use ($username, $withPhoto, $largePhoto, $onComplete) {
                        $this->getInfoByUsername($username, $withPhoto, $largePhoto, $onComplete);
                    });
                } else {
                    $this->getFullUserInfo($user, $withPhoto, $largePhoto, $onComplete);
                }
            } else {
                $onComplete($user);
            }
        });
    }

    /**
     * @param ContactUser $user
     * @param bool        $withPhoto
     * @param bool        $largePhoto
     * @param callable    $onComplete function(UserInfoModel $model)
     *
     * @throws TGException
     */
    private function buildUserInfoModel(ContactUser $user, bool $withPhoto, bool $largePhoto, callable $onComplete): void
    {
        $userModel = new UserInfoModel();
        $userModel->id = $user->getUserId();
        $userModel->phone = $user->getPhone();
        $userModel->username = $user->getUsername();
        $userModel->status = $this->createUserStatusModel($user->getStatus());
        $userModel->accessHash = $user->getAccessHash();
        $userModel->firstName = $user->getFirstName();
        $userModel->lastName = $user->getLastName();
        $userModel->langCode = $user->getLangCode();

        if ($withPhoto) {
            $this->createUserPictureModel($user, $largePhoto, static function ($photo) use ($userModel, $onComplete) {
                $userModel->photo = $photo;
                $onComplete($userModel);
            });
        } else {
            $onComplete($userModel);
        }
    }

    private function extendUserInfoModel(UserInfoModel $model, UserFull $userFull): UserInfoModel
    {
        $model->bio = $userFull->getAbout();
        $model->commonChatsCount = $userFull->getCommonChatsCount();

        return $model;
    }

    /**
     * @param ContactUser $user
     * @param bool        $largePhotos
     * @param callable    $onPictureLoaded function(?PictureModel $model)
     *
     * @throws TGException
     */
    private function createUserPictureModel(ContactUser $user, bool $largePhotos, callable $onPictureLoaded): void
    {
        $profilePhoto = $user->getPhoto();
        if (!$profilePhoto) {
            $onPictureLoaded(null);

            return;
        }

        $photo = $largePhotos ?
            $profilePhoto->getBigPhoto() :
            $profilePhoto->getSmallPhoto();

        $photoLocation = null;
        $dcId = null;

        if ($profilePhoto instanceof UserProfilePhoto && $profilePhoto->isV2()) {
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
            $photoLocation = new input_file_location(
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
     *
     * @return UserStatusModel
     */
    private function createUserStatusModel($userStatus): ?UserStatusModel
    {
        if (!$userStatus) {
            return null;
        }

        $statusModel = new UserStatusModel();
        $statusModel->is_online = $userStatus->isOnline();
        $statusModel->is_hidden = $userStatus->isHidden();
        $statusModel->was_online = $userStatus->getWasOnline();
        $statusModel->expires = $userStatus->getExpires();

        return $statusModel;
    }

    /**
     * @param FileModel $model
     * @param callable  $onPictureLoaded function(?PictureModel $model)
     *
     * @throws TGException
     */
    public function loadFile(FileModel $model, callable $onPictureLoaded): void
    {
        $locationRequest = new input_photofilelocation(
            $model->getId(),
            $model->getAccessHash(),
            $model->getFileReference(),
            $model->getSizeId()
        );
        $this->readPicture($locationRequest, $model->getDcId(), $onPictureLoaded);
    }

    /**
     * @param TLClientMessage $fileLocation
     * @param int             $photoDcId
     * @param callable        $onPictureLoaded function(?PictureModel $model)
     *
     * @throws TGException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    private function readPicture(TLClientMessage $fileLocation, int $photoDcId, callable $onPictureLoaded): void
    {
        $isCurrentDc = $photoDcId === $this->basicClient->getConnection()->getDCInfo()->getDcId();
        if ($isCurrentDc) {
            $this->readPictureFromCurrentDC($this->basicClient->getConnection(), $fileLocation, $onPictureLoaded);
        } else {
            $this->readPictureFromForeignDC($fileLocation, $photoDcId, $onPictureLoaded);
        }
    }

    /**
     * @param SocketMessenger   $basicClient
     * @param TLClientMessage   $location
     * @param callable          $onPictureLoaded function(?PictureModel $model)
     * @param PictureModel|null $picModel
     * @param int               $offset
     */
    private function readPictureFromCurrentDC(
        SocketMessenger $basicClient,
        TLClientMessage $location,
        callable $onPictureLoaded,
        $picModel = null,
        int $offset = 0
    ): void {
        if (!$picModel) {
            $picModel = $picModel = new PictureModel();
        }

        $request = new get_file($location, $offset, self::READ_LIMIT_BYTES);
        $basicClient->getResponseAsync($request, function (AnonymousMessage $message) use ($basicClient, $location, $onPictureLoaded, $picModel) {
            $response = new UploadedFile($message);
            if (!$response->isJpeg()) {
                throw new TGException(TGException::ERR_CLIENT_USER_PIC_UNKNOWN_FORMAT);
            }
            $readBytes = $response->getBytes();
            $readBytesCount = strlen($readBytes);

            $picModel->bytes .= $readBytes;
            $picModel->modificationTime = $response->getModificationTs();
            $picModel->format = $response->getFormatName();

            if ($readBytesCount < self::READ_LIMIT_BYTES) {
                $onPictureLoaded($picModel);
            } else {
                $this->readPictureFromCurrentDC($basicClient, $location, $onPictureLoaded, $picModel, strlen($picModel->bytes));
            }
        });
    }

    public function warmup(): void
    {
        if (!$this->authKeyCache || !$this->authKeyCache->empty()) {
            return;
        }
        $this->basicClient->getConnection()->getResponseAsync(new get_config(), function (AnonymousMessage $message) {
            $dcConfigs = new DcConfigApp($message);

            $dcFound = [];
            foreach ($dcConfigs->getDataCenters() as $dc) {
                if (!isset($dcFound[$dc->getId()])
                    && $this->basicClient->getConnection()->isDcAppropriate($dc)
                    && $dc->getId() !== $this->authKey->getAttachedDC()->getDcId()
                ) {
                    $dcFound[$dc->getId()] = 1;
                    $dc = new DataCentre($dc->getIp(), $dc->getId(), $dc->getPort());
                    $this->getAuthKey($dc, static function () { });
                }
            }
        });
    }

    /**
     * @param TLClientMessage $location
     * @param int             $photoDcId
     * @param callable        $onPictureLoaded function(?PictureModel $model)
     */
    private function readPictureFromForeignDC(TLClientMessage $location, int $photoDcId, callable $onPictureLoaded): void
    {
        $this->basicClient->getConnection()->getResponseAsync(new get_config(), function (AnonymousMessage $message) use ($location, $photoDcId, $onPictureLoaded) {
            $dcConfigs = new DcConfigApp($message);

            $dcFound = false;
            foreach ($dcConfigs->getDataCenters() as $dc) {
                if ($dc->getId() === $photoDcId && $this->basicClient->getConnection()->isDcAppropriate($dc)) {
                    $dcFound = true;

                    // create authKey in foreign dc
                    $dc = new DataCentre($dc->getIp(), $dc->getId(), $dc->getPort());
                    $onAuthKeyReady = $this->getPictureLoadingCallback($onPictureLoaded, $dc, $location);
                    $this->getAuthKey($dc, $onAuthKeyReady);

                    break;
                }
            }

            if (!$dcFound) {
                throw new TGException(TGException::ERR_CLIENT_PICTURE_ON_UNREACHABLE_DC);
            }
        });
    }

    /**
     * @param DataCentre $dc
     * @param callable   $cb
     *
     * @throws TGException
     */
    private function getAuthKey(DataCentre $dc, callable $cb): void
    {
        $cacheKey = (string) $dc->getDcId();
        $cachedAuthKeySerialized = $this->authKeyCache->get($cacheKey);
        if ($cachedAuthKeySerialized !== null) {
            $cb(AuthKeyCreator::createFromString($cachedAuthKeySerialized));
        } else {
            $auth = new AppAuthorization($dc, $this->proxy);
            $this->notEncryptedClients[] = $auth;
            $lastIndex = array_key_last($this->notEncryptedClients);
            $auth->createAuthKey(function (AuthKey $authKey) use ($cb, $lastIndex, $cacheKey) {
                unset($this->notEncryptedClients[$lastIndex]);
                $this->authKeyCache->set($cacheKey, $authKey->getSerializedAuthKey());
                $cb($authKey);
            });
        }
    }

    public function terminate(): void
    {
        $this->basicClient->terminate();
    }

    /**
     * @param callable        $onPictureLoaded
     * @param DataCentre      $dc
     * @param TLClientMessage $location
     *
     * @return Closure
     */
    private function getPictureLoadingCallback(callable $onPictureLoaded, DataCentre $dc, TLClientMessage $location): Closure
    {
        return function (AuthKey $authKey) use ($onPictureLoaded, $dc, $location) {
            // login in foreign dc
            $clientKey = count($this->otherDcClients);
            $this->otherDcClients[$clientKey] = $this->generator->generate(false, true);
            $this->otherDcClients[$clientKey]->login(
                $authKey,
                $this->proxy,
                function () use ($dc, $location, $onPictureLoaded, $clientKey) {
                    // export current authorization to foreign dc
                    $exportAuthRequest = new export_authorization($dc->getDcId());
                    $this->basicClient->getConnection()->getResponseAsync(
                        $exportAuthRequest,
                        function (AnonymousMessage $message) use ($clientKey, $location, $onPictureLoaded) {
                            $exportedAuthResponse = new ExportedAuthorization($message);

                            // import authorization on foreign dc
                            $importAuthRequest = new import_authorization(
                                $exportedAuthResponse->getUserId(),
                                $exportedAuthResponse->getTransferKey()
                            );
                            $this->otherDcClients[$clientKey]->getConnection()->getResponseAsync($importAuthRequest, function (AnonymousMessage $message) use ($exportedAuthResponse, $clientKey, $location, $onPictureLoaded) {
                                $authorization = new AuthorizationSelfUser($message);
                                if ($authorization->getUser()->getUserId() !== $exportedAuthResponse->getUserId()) {
                                    throw new TGException(TGException::ERR_AUTH_EXPORT_FAILED);
                                }
                                // make foreign dc current and get the picture
                                $this->readPictureFromCurrentDC($this->otherDcClients[$clientKey]->getConnection(), $location, function ($picture) use ($clientKey, $onPictureLoaded) {
                                    $this->otherDcClients[$clientKey]->terminate();
                                    unset($this->otherDcClients[$clientKey]);
                                    $onPictureLoaded($picture);
                                });
                            });
                        }
                    );
                }
            );
        };
    }
}
