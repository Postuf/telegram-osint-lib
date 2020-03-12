<?php

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\MTSerialization\OwnImplementation\OwnAnonymousMessage;

class AnonymousMessageMock implements AnonymousMessage
{
    /**
     * @var AnonymousMessage
     */
    private $impl;

    /**
     * @param array $message
     *
     * @throws TGException
     */
    public function __construct(array $message)
    {
        $this->impl = new OwnAnonymousMessage($message);
    }

    /**
     * Return named node from current object
     *
     * @param string $name
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    public function getNode(string $name): AnonymousMessage
    {
        return $this->impl->getNode($name);
    }

    /**
     * Return array of nodes under the $name from current object
     *
     * @param string $name
     *
     * @throws TGException
     *
     * @return AnonymousMessage[]
     */
    public function getNodes(string $name): array
    {
        return $this->impl->getNodes($name);
    }

    /**
     * Return array of scalars under the $name from current object
     *
     * @param string $name
     *
     * @throws TGException
     *
     * @return array
     */
    public function getScalars(string $name): array
    {
        return $this->impl->getScalars($name);
    }

    /**
     * Get message name
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->impl->getType();
    }

    /**
     * Get value of named field from current object
     *
     * @param string $name
     *
     * @throws TGException
     *
     * @return int|string|array
     */
    public function getValue(string $name)
    {
        return $this->impl->getValue($name);
    }

    /**
     * @return string
     */
    public function getPrintable(): string
    {
        return $this->impl->getPrintable();
    }

    /**
     * @return string
     */
    public function getDebugPrintable(): string
    {
        return $this->impl->getDebugPrintable();
    }

    /**
     * @param int $userId
     * @param int $expires
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    public static function getUserOnline(int $userId, int $expires = 0): AnonymousMessage
    {
        return new self([
            '_'      => 'updateShort',
            'update' => [
                '_'       => 'updateUserStatus',
                'user_id' => $userId,
                'status'  => [
                    '_'       => 'userStatusOnline',
                    'expires' => $expires,
                ],
            ],
            'date' => 1533376561,
        ]);
    }

    /**
     * @param int $userId
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    public static function getUserOffline(int $userId): AnonymousMessage
    {
        return new self([
            '_'      => 'updateShort',
            'update' => [
                '_'       => 'updateUserStatus',
                'user_id' => $userId,
                'status'  => [
                    '_'          => 'userStatusOffline',
                    'was_online' => 1533376861,
                ],
            ],
            'date' => 1533376561,
        ]);
    }

    /**
     * @param int $userId
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    public static function getUserEmpty(int $userId): AnonymousMessage
    {
        return new self([
            '_'      => 'updateShort',
            'update' => [
                '_'       => 'updateUserStatus',
                'user_id' => $userId,
                'status'  => [
                    '_' => 'userStatusEmpty',
                ],
            ],
            'date' => 1533376561,
        ]);
    }

    /**
     * @param int $userId
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    public static function getUserRecently(int $userId): AnonymousMessage
    {
        return new self([
            '_'      => 'updateShort',
            'update' => [
                '_'       => 'updateUserStatus',
                'user_id' => $userId,
                'status'  => [
                    '_' => 'userStatusRecently',
                ],
            ],
            'date' => 1533376561,
        ]);
    }

    /**
     * @param int    $id
     * @param string $phone
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    public static function getContact(int $id, string $phone): AnonymousMessage
    {
        return new self([
            '_'                         => 'user',
            'id'                        => $id,
            'access_hash'               => 2811936216873835544,
            'first_name'                => 'name_89169904863',
            'last_name'                 => 'l_f4d6bed238',
            'username'                  => 'AseN_17',
            'phone'                     => $phone,
            'photo'                     => [
                '_'           => 'userProfilePhoto',
                'photo_id'    => 806194743786710955,
                'photo_small' => [
                    '_'         => 'fileLocation',
                    'dc_id'     => 2,
                    'volume_id' => 225517222,
                    'local_id'  => 141372,
                    'secret'    => 4952891847968332097,
                ],

                'photo_big' => [
                    '_'         => 'fileLocation',
                    'dc_id'     => 2,
                    'volume_id' => 225517222,
                    'local_id'  => 141374,
                    'secret'    => -5785720690880313215,
                ],

            ],

            'status' => [
                '_'       => 'userStatusOnline',
                'expires' => 1533377307,
            ],
        ]);
    }

    /**
     * @param int    $userId
     * @param string $userPhone
     * @param string $status
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    public static function getImportedContact(int $userId, string $userPhone, string $status): AnonymousMessage
    {
        $statusObj = '';

        switch ($status){
            case 'offline':
                $statusObj = [
                    '_'          => 'userStatusOffline',
                    'was_online' => 1533638872,
                ];
                break;
            case 'online':
                $statusObj = [
                    '_'       => 'userStatusOnline',
                    'expires' => 1533638872,
                ];
                break;
            case 'empty':
                $statusObj = [
                    '_' => 'userStatusEmpty',
                ];
                break;
            case 'recently':
                $statusObj = [
                    '_' => 'userStatusRecently',
                ];
                break;
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        return new self([
            '_'        => 'contacts.importedContacts',
            'imported' => [
                '0' => [
                    'user_id'   => $userId,
                    'client_id' => 8,
                ],
            ],

            'popular_invites' => [
            ],

            'retry_contacts' => [
            ],

            'users' => [
                '0' => [
                    '_'           => 'user',
                    'bit_mask'    => 100001111111,
                    'id'          => $userId,
                    'access_hash' => 2811936216873835544,
                    'first_name'  => 'name_89169904863',
                    'last_name'   => 'l_1abe970cb9',
                    'username'    => 'AseN_17',
                    'phone'       => $userPhone,
                    'photo'       => [
                        '_'           => 'userProfilePhoto',
                        'photo_id'    => 806194743786710955,
                        'photo_small' => [
                            '_'         => 'fileLocation',
                            'dc_id'     => 2,
                            'volume_id' => 225517222,
                            'local_id'  => 141372,
                            'secret'    => 4952891847968332097,
                        ],

                        'photo_big' => [
                            '_'         => 'fileLocation',
                            'dc_id'     => 2,
                            'volume_id' => 225517222,
                            'local_id'  => 141374,
                            'secret'    => -5785720690880313215,
                        ],

                    ],

                    'status' => $statusObj,
                ],
            ],
        ]);
    }
}
