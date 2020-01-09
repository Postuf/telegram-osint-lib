<?php

declare(strict_types=1);

namespace Scenario;

use Client\InfoObtainingClient\Models\FileModel;
use Client\InfoObtainingClient\Models\PictureModel;
use Closure;
use Exception;
use Exception\TGException;
use InvalidArgumentException;
use Logger\Logger;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\ClientMessages\Api\get_all_chats;
use TLMessage\TLMessage\ClientMessages\Api\get_history;

/**
 * Downloading group photos
 *
 * This example requires info user (second authkey)
 * to be member of a group, otherwise it is useless.
 *
 * @see get_all_chats
 * @see get_history
 */
class GroupPhotosClient extends MyTgClientDebug implements ScenarioInterface
{
    /** @var int|null */
    private $groupId;
    /** @var string|null */
    private $since;
    /** @var string|null */
    private $to;
    /** @var string|null */
    private $deepLink;
    /** @var callable */
    private $saveHandler;

    /**
     * @param int|null      $groupId
     * @param string|null   $since
     * @param string|null   $to
     * @param callable|null $saveHandler function(PictureModel $model, int $id)
     *
     * @throws TGException
     */
    public function __construct(?int $groupId = null, ?string $since = null, ?string $to = null, ?callable $saveHandler = null)
    {
        parent::__construct();
        $this->groupId = $groupId;
        $this->since = $since;
        $this->to = $to;
        $this->saveHandler = $saveHandler;
    }

    public function setDeepLink(string $deepLink): void
    {
        $this->deepLink = $deepLink;
    }

    private function getSinceTs(): int
    {
        if (!$this->since) {
            return 0;
        }
        $fmt = 'YYYYmmdd';
        if (strlen($this->since) !== strlen($fmt)) {
            throw new InvalidArgumentException("invalid since format, use $fmt");
        }
        $y = substr($this->since, 0, 4);
        $m = substr($this->since, 4, 2);
        $d = substr($this->since, 6, 2);

        return strtotime("$y-$m-$d 00:00:00");
    }

    private function getToTs(): int
    {
        if (!$this->to) {
            return 0;
        }
        $fmt = 'YYYYmmdd';
        if (strlen($this->to) !== strlen($fmt)) {
            throw new InvalidArgumentException("invalid to format, use $fmt");
        }
        $y = substr($this->to, 0, 4);
        $m = substr($this->to, 4, 2);
        $d = substr($this->to, 6, 2);

        return strtotime("$y-$m-$d 00:00:00");
    }

    /**
     * @param string|null $type
     *
     * @throws TGException
     */
    public function listChats(?string $type = null)
    {
        $this->infoLogin();
        Logger::log(__CLASS__, 'listing all chats');
        $this->infoClient->getAllChats(function (AnonymousMessage $message) use ($type) {
            /** @see https://core.telegram.org/constructor/messages.chats */
            $chats = $message->getNodes('chats');
            $chatCount = count($chats);
            Logger::log(__CLASS__, "got $chatCount chats/channels");
            foreach ($chats as $chatNode) {
                $id = (int) $chatNode->getValue('id');
                $currentType = $chatNode->getType();
                if ($type && $type != $currentType) {
                    continue;
                }
                $title = $chatNode->getValue('title');
                Logger::log(__CLASS__, "got chat '$title' with id $id of type $currentType");
            }
        });

        $this->pollAndTerminate();
    }

    /**
     * @throws TGException
     */
    public function startActions()
    {
        $this->infoLogin();
        /** @var array $ids */
        $ids = [];
        $limit = 200;
        sleep(1);
        if ($this->deepLink) {
            Logger::log(__CLASS__, "getting chat by deeplink {$this->deepLink}");
            $parts = explode('/', $this->deepLink);
            $username = $parts[count($parts) - 1];
            $this->infoClient->resolveUsername($username, function (AnonymousMessage $message) use ($limit) {
                if ($message->getType() !== 'contacts.resolvedPeer') {
                    Logger::log(__CLASS__, 'got unexpected response of type '.$message->getType());

                    return;
                }
                /** @var array $peer */
                $peer = $message->getValue('peer');
                if ($peer['_'] !== 'peerChannel') {
                    Logger::log(__CLASS__, 'got unexpected peer of type '.$peer['_']);

                    return;
                }

                $chats = $message->getValue('chats');
                foreach ($chats as $chat) {
                    $id = (int) $chat['id'];
                    $handler = $this->makeChatMessagesHandler($id, $limit);
                    /** @var array $chat */
                    Logger::log(__CLASS__, "getting channel messages with limit $limit");
                    $this->infoClient->getChannelMessages(
                        (int) $chat['id'],
                        (int) $chat['access_hash'],
                        $limit,
                        0,
                        0,
                        $handler
                    );

                }
            });
        } else {
            Logger::log(__CLASS__, 'getting all chats');
            $this->infoClient->getAllChats(function (AnonymousMessage $message) use (&$ids, $limit) {
                /** @see https://core.telegram.org/constructor/messages.chats */
                $chats = $message->getNodes('chats');
                $chatCount = count($chats);
                Logger::log(__CLASS__, "got $chatCount chats");
                foreach ($chats as $chatNode) {
                    $id = (int) $chatNode->getValue('id');
                    $ids[] = $id;
                    if ($this->groupId && $this->groupId != $id) {
                        continue;
                    }

                        if ($chatNode->getType() != 'chat' && !$this->groupId) {
                            continue;
                        }

                        $handler = $this->makeChatMessagesHandler($id, $limit);
                        Logger::log(__CLASS__, "parsing {$chatNode->getType()} $id with limit $limit");
                        if ($chatNode->getType() == 'chat') {
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
                                $id,
                                $accessHash,
                                $limit,
                                0,
                                0,
                                $handler
                            );
                        }
                    }
                }
            );
        }

        $this->pollAndTerminate();
    }

    /**
     * @param FileModel $model
     * @param callable  $saveFile function(PictureModel $model, int $id)
     *
     * @throws TGException
     */
    private function getFile(FileModel $model, callable $saveFile): void
    {
        usleep(100000);
        $this->infoClient->loadFile($model, function (PictureModel $pictureModel) use ($model, $saveFile) {
            $id = $model->getId();
            $saveFile($pictureModel, $id);
        });
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
                if ($node->getType() !== 'message' || !($hasMedia = $node->getValue('media'))) {
                    Logger::log(
                        __CLASS__,
                        $node->getType() !== 'message'
                            ? 'Unsupported msg node type '.$node->getType().' with id '.$node->getValue('id')
                            : 'Node without media with id '.$node->getValue('id')
                    );
                    continue;
                }

                $lastId = (int) $node->getValue('id');

                if ($this->getSinceTs() && $node->getValue('date') < $this->getSinceTs()) {
                    continue;
                }

                if ($this->getToTs() && $node->getValue('date') > $this->getToTs()) {
                    continue;
                }

                /* https://core.telegram.org/type/MessageMedia */
                /** @var array $media */
                $media = $node->getValue('media');
                if ($media['_'] !== 'messageMediaPhoto') {
                    continue;
                }

                /* https://core.telegram.org/type/Photo */
                $photo = $media['photo'];
                $sizeId = 'x';
                $types = ['photoStrippedSize', 'photoSize'];
                foreach ($types as $type) {
                    foreach ($photo['sizes'] as $size) {
                        if ($type == $size['_']) {
                            $sizeId = $size['type'];
                        }
                    }
                }
                if (!$sizeId) {
                    throw new Exception('Invalid photo: no sizes: '.json_encode($photo));
                }
                sleep(1);
                Logger::log(__CLASS__, 'getting file '.$photo['id']);
                $saveHandler = $this->saveHandler ?: function (PictureModel $pictureModel, int $id) {
                    $filename = "$id.".$pictureModel->format;
                    file_put_contents($filename, $pictureModel->bytes);
                    Logger::log(__CLASS__, "$filename saved");
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
                sleep(1);
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
