<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use Closure;
use RuntimeException;
use TelegramOSINT\Client\InfoObtainingClient\Models\FileModel;
use TelegramOSINT\Client\InfoObtainingClient\Models\GroupId;
use TelegramOSINT\Client\InfoObtainingClient\Models\PictureModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\Scenario\Models\OptionalDateRange;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_all_chats;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_history;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ResolvedPeer;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Peer\PeerUser;
use TelegramOSINT\Tools\Proxy;

/**
 * Downloading group photos
 *
 * @see get_all_chats
 * @see get_history
 */
class GroupPhotosScenario extends AbstractGroupScenario
{
    private const FIELD_MESSAGE_DATE = 'date';
    private const FIELD_MESSAGE_MEDIA = 'media';

    /** @var int|null */
    private ?int $since;
    /** @var int|null */
    private ?int $to;
    /** @var callable */
    private $saveHandler;
    /** @var string|null */
    private ?string $username;
    /** @var int|null */
    private ?int $userId = null;

    /**
     * @param OptionalDateRange             $dateRange
     * @param string|null                   $username
     * @param callable|null                 $saveHandler function(PictureModel $model, int $id)
     * @param ClientGeneratorInterface|null $generator
     * @param Proxy|null                    $proxy
     *
     * @throws TGException
     */
    public function __construct(
        OptionalDateRange $dateRange,
        ?string $username = null,
        ?callable $saveHandler = null,
        ?ClientGeneratorInterface $generator = null,
        ?Proxy $proxy = null
    ) {
        parent::__construct($generator, $proxy);
        $this->since = $dateRange->getSince();
        $this->to = $dateRange->getTo();
        $this->saveHandler = $saveHandler;
        $this->username = $username;
    }

    private function getSinceTs(): int
    {
        return (int) $this->since;
    }

    private function getToTs(): int
    {
        return (int) $this->to;
    }

    /**
     * @param string|null $type
     *
     * @throws TGException
     */
    public function listChats(?string $type = null): void
    {
        $pollAndTerminate = true;

        $this->authAndPerformActions(function () use ($type): void {
            Logger::log(__CLASS__, 'listing all chats');
            $this->infoClient->getAllChats(static function (AnonymousMessage $message) use ($type) {
                /** @see https://core.telegram.org/constructor/messages.chats */
                $chats = $message->getNodes('chats');
                $chatCount = count($chats);
                Logger::log(__CLASS__, "got $chatCount chats/channels");
                foreach ($chats as $chatNode) {
                    $id = (int) $chatNode->getValue('id');
                    $currentType = $chatNode->getType();
                    if ($type && $type !== $currentType) {
                        continue;
                    }
                    $title = $chatNode->getValue('title');
                    Logger::log(__CLASS__, "got chat '$title' with id $id of type $currentType");
                }
            });
        }, $pollAndTerminate);
    }

    /**
     * @param FileModel $model
     * @param callable  $saveFile function(PictureModel $model, int $id)
     *
     * @throws TGException
     */
    private function getFile(FileModel $model, callable $saveFile): void
    {
        $this->infoClient->loadFile($model, static function (PictureModel $pictureModel) use ($model, $saveFile) {
            $id = $model->getId();
            $saveFile($pictureModel, $id);
        });
    }

    private function getAllChatsHandler(int $limit): callable
    {
        return function (AnonymousMessage $message) use ($limit) {
            /** @see https://core.telegram.org/constructor/messages.chats */
            $chats = $message->getNodes('chats');
            $chatCount = count($chats);
            Logger::log(__CLASS__, "got $chatCount chats");
            foreach ($chats as $chatNode) {
                $id = (int) $chatNode->getValue('id');
                if ($this->groupId && $this->groupId !== $id) {
                    continue;
                }

                if (!$this->groupId && $chatNode->getType() !== 'chat') {
                    continue;
                }

                $handler = $this->makeChatMessagesHandler($id, $limit);
                Logger::log(__CLASS__, "parsing {$chatNode->getType()} $id with limit $limit");
                if ($chatNode->getType() === 'chat') {
                    $this->infoClient->getChatMessages(
                        $id,
                        $limit,
                        0,
                        0,
                        $handler
                    );
                } else {
                    $accessHash = $chatNode->getValue('access_hash');
                    $this->infoClient->getChannelMessages(
                        new GroupId($id, $accessHash),
                        $limit,
                        0,
                        0,
                        $handler
                    );
                }
            }
        };
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $actions = function (): void {
            /** @var array $ids */
            $limit = 200;
            usleep(10000);
            if ($this->deepLink) {
                Logger::log(__CLASS__, "getting chat by deeplink {$this->deepLink}");
                $parts = explode('/', $this->deepLink);
                $groupname = $parts[count($parts) - 1];

                $afterGroupResolve = function (AnonymousMessage $message) use ($limit) {
                    foreach ($message->getValue('chats') as $chat) {
                        $id = (int) $chat['id'];
                        $handler = $this->makeChatMessagesHandler($id, $limit);
                        /** @var array $chat */
                        $this->infoClient->getChannelMessages(
                            new GroupId((int) $chat['id'], (int) $chat['access_hash']),
                            $limit,
                            0,
                            0,
                            $handler
                        );
                    }
                };

                if ($this->username) {
                    $onUserResolve = function (AnonymousMessage $message) use ($groupname, $afterGroupResolve) {
                        if (!ResolvedPeer::isIt($message)) {
                            Logger::log(__CLASS__, 'got unexpected response of type '.$message->getType());

                            return;
                        }

                        /** @var array $peer */
                        $peer = (new ResolvedPeer($message))->getPeer();
                        /** @see https://core.telegram.org/constructor/peerUser */
                        if (!($peer instanceof PeerUser)) {
                            Logger::log(__CLASS__, 'got unexpected peer type');

                            return;
                        }

                        $this->userId = $peer->getId();

                        $this->infoClient->resolveUsername($groupname, $this->getResolveHandler($afterGroupResolve));
                    };

                    $this->infoClient->resolveUsername($this->username, $onUserResolve);
                } else {
                    $this->infoClient->resolveUsername($groupname, $this->getResolveHandler($afterGroupResolve));
                }
            } else {
                Logger::log(__CLASS__, 'getting all chats');
                $this->infoClient->getAllChats($this->getAllChatsHandler($limit));
            }
        };

        $this->authAndPerformActions($actions, $pollAndTerminate);
    }

    /**
     * @param int $id
     * @param int $limit
     *
     * @return Closure
     */
    private function makeChatMessagesHandler(int $id, int $limit = 1): Closure
    {
        return function (AnonymousMessage $message) use ($id, $limit) {
            /* https://core.telegram.org/type/messages.Messages */
            $nodes = $message->getNodes('messages');
            if ($message->getType() === 'messages.messagesSlice') {
                Logger::log(
                    __CLASS__,
                    'Got slice of '.count($nodes).
                    ' total: '.$message->getValue('count').
                    " limit: $limit"
                );
            } else {
                Logger::log(__CLASS__, 'got messages of '.count($nodes).' nodes with limit '.$limit);
            }
            $lastId = null;
            foreach ($nodes as $node) {
                /** @see https://core.telegram.org/constructor/message */
                $lastId = (int) $node->getValue('id');

                if ($node->getType() !== 'message' || !($node->getValue(self::FIELD_MESSAGE_MEDIA))) {
                    Logger::log(
                        __CLASS__,
                        $node->getType() !== 'message'
                            ? 'Unsupported msg node type '.$node->getType().' with id '.$node->getValue('id')
                            : 'Node without media with id '.$node->getValue('id')
                    );
                    continue;
                }

                if ($this->userId && $node->getValue('from_id') !== $this->userId) {
                    continue;
                }

                if ($this->getSinceTs() && $node->getValue(self::FIELD_MESSAGE_DATE) < $this->getSinceTs()) {
                    continue;
                }

                if ($this->getToTs() && $node->getValue(self::FIELD_MESSAGE_DATE) > $this->getToTs()) {
                    continue;
                }

                /* https://core.telegram.org/type/MessageMedia */
                /** @var array $media */
                $media = $node->getValue(self::FIELD_MESSAGE_MEDIA);
                if ($media['_'] !== 'messageMediaPhoto') {
                    continue;
                }

                /* https://core.telegram.org/type/Photo */
                $photo = $media['photo'];
                $sizeId = 'x';
                foreach (['photoStrippedSize', 'photoSize'] as $type) {
                    foreach ($photo['sizes'] as $size) {
                        if ($type === $size['_']) {
                            $sizeId = $size['type'];
                        }
                    }
                }
                if (!$sizeId) {
                    throw new RuntimeException('Invalid photo: no sizes: '.json_encode($photo, JSON_THROW_ON_ERROR));
                }
                usleep(10000);
                Logger::log(__CLASS__, 'getting file '.$photo['id']);
                $saveHandler = $this->saveHandler ?: static function (PictureModel $pictureModel, int $id) {
                    $filename = "$id.".$pictureModel->format;
                    file_put_contents($filename, $pictureModel->bytes);
                    Logger::log(
                        __CLASS__,
                        "$filename saved with time ".
                        date('Y-m-d H:i:s', $pictureModel->modificationTime).
                        ' '.date_default_timezone_get()
                    );
                };
                $fileModel = new FileModel(
                    (int) $photo['id'],
                    (int) $photo['access_hash'],
                    $photo['file_reference'],
                    $sizeId,
                    (int) $photo['dc_id']
                );
                $this->getFile($fileModel, $saveHandler);
            }

            if (count($nodes) === $limit) {
                usleep(10000);
                Logger::log(__CLASS__, "Got more messages, iterate after $lastId");
                $this->infoClient->getChatMessages(
                    $id,
                    $limit,
                    0,
                    $lastId,
                    $this->makeChatMessagesHandler($id, $limit)
                );
            }
        };
    }
}
