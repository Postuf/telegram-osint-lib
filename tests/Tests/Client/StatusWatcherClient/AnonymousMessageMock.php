<?php

namespace Tests\Tests\Client\StatusWatcherClient;


use Exception\TGException;
use MTSerialization\AnonymousMessage;
use MTSerialization\OwnImplementation\OwnAnonymousMessage;


class AnonymousMessageMock implements AnonymousMessage
{
    /**
     * @var AnonymousMessage
     */
    private $impl;


    /**
     * @param array $message
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
     * @return AnonymousMessage
     * @throws TGException
     */
    public function getNode(string $name)
    {
        return $this->impl->getNode($name);
    }

    /**
     * Return array of nodes under the $name from current object
     *
     * @param string $name
     * @return AnonymousMessage[]
     * @throws TGException
     */
    public function getNodes(string $name)
    {
        return $this->impl->getNodes($name);
    }

    /**
     * Get message name
     *
     * @return string
     */
    public function getType()
    {
        return $this->impl->getType();
    }

    /**
     * Get value of named field from current object
     *
     * @param string $name
     * @return int|string|array
     * @throws TGException
     */
    public function getValue(string $name)
    {
        return $this->impl->getValue($name);
    }

    /**
     * @return string
     */
    public function getPrintable()
    {
        return $this->impl->getPrintable();
    }

    /**
     * @return string
     */
    public function getDebugPrintable()
    {
        return $this->impl->getDebugPrintable();
    }


    /**
     * @param int $userId
     * @param int $expires
     * @return AnonymousMessage
     *
     * @throws TGException
     */
    public static function getUserOnline(int $userId, int $expires = 0)
    {
        return new AnonymousMessageMock([
            '_' => 'updateShort',
            'update' => [
                '_' => 'updateUserStatus',
                'user_id' => $userId,
                'status' => [
                    '_' => 'userStatusOnline',
                    'expires' => $expires
                ]
            ],
            'date' => 1533376561
        ]);
    }


    /**
     * @param int $userId
     * @return AnonymousMessage
     * @throws TGException
     */
    public static function getUserOffline(int $userId)
    {
        return new AnonymousMessageMock([
            '_' => 'updateShort',
            'update' => [
                '_' => 'updateUserStatus',
                'user_id' => $userId,
                'status' => [
                    '_' => 'userStatusOffline',
                    'was_online' => 1533376861
                ]
            ],
            'date' => 1533376561
        ]);
    }


    /**
     * @param int $userId
     * @return AnonymousMessage
     * @throws TGException
     */
    public static function getUserEmpty(int $userId)
    {
        return new AnonymousMessageMock([
            '_' => 'updateShort',
            'update' => [
                '_' => 'updateUserStatus',
                'user_id' => $userId,
                'status' => [
                    '_' => 'userStatusEmpty'
                ]
            ],
            'date' => 1533376561
        ]);
    }


    /**
     * @param int $userId
     * @return AnonymousMessage
     * @throws TGException
     */
    public static function getUserRecently(int $userId)
    {
        return new AnonymousMessageMock([
            '_' => 'updateShort',
            'update' => [
                '_' => 'updateUserStatus',
                'user_id' => $userId,
                'status' => [
                    '_' => 'userStatusRecently'
                ]
            ],
            'date' => 1533376561
        ]);
    }


    /**
     * @param int $id
     * @param string $phone
     * @return AnonymousMessage
     * @throws TGException
     */
    public static function getContact(int $id, string $phone)
    {
        return new AnonymousMessageMock([
            '_' => 'user',
            'id' => $id,
            'access_hash' => 2811936216873835544,
            'first_name' => 'name_89169904863',
                            'last_name' => 'l_f4d6bed238',
                            'username' => 'AseN_17',
                            'phone' => $phone,
                            'photo' => [
                                    '_' => 'userProfilePhoto',
                                    'photo_id' => 806194743786710955,
                                    'photo_small' => [
                                            '_' => 'fileLocation',
                                            'dc_id' => 2,
                                            'volume_id' => 225517222,
                                            'local_id' => 141372,
                                            'secret' => 4952891847968332097
                                        ],

                                    'photo_big' => [
                                            '_' => 'fileLocation',
                                            'dc_id' => 2,
                                            'volume_id' => 225517222,
                                            'local_id' => 141374,
                                            'secret' => -5785720690880313215
                                        ],

                                ],

                            'status' => [
                                    '_' => 'userStatusOnline',
                                    'expires' => 1533377307
                                ]
                        ]);
    }


    public static function getImportedContact($userId, $userPhone, $status)
    {
        $statusObj = '';

        switch ($status){
            case 'offline':
                $statusObj = [
                    '_' => 'userStatusOffline',
                    'was_online' => 1533638872
                ];
                break;
            case 'online':
                $statusObj = [
                    '_' => 'userStatusOnline',
                    'expires' => 1533638872
                ];
                break;
            case 'empty':
                $statusObj = [
                    '_' => 'userStatusEmpty'
                ];
                break;
            case 'recently':
                $statusObj = [
                    '_' => 'userStatusRecently'
                ];
                break;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return new AnonymousMessageMock([
            '_' => 'contacts.importedContacts',
            'imported' => [
                '0' => [
                    'user_id' => $userId,
                    'client_id' => 8
                ]
            ],

            'popular_invites' => [
            ],

            'retry_contacts' => [
            ],

            'users' => [
                '0' => [
                    '_' => 'user',
                    'bit_mask' => 100001111111,
                    'id' => $userId,
                    'access_hash' => 2811936216873835544,
                    'first_name' => 'name_89169904863',
                    'last_name' => 'l_1abe970cb9',
                    'username' => 'AseN_17',
                    'phone' => $userPhone,
                    'photo' => [
                        '_' => 'userProfilePhoto',
                        'photo_id' => 806194743786710955,
                        'photo_small' => [
                            '_' => 'fileLocation',
                            'dc_id' => 2,
                            'volume_id' => 225517222,
                            'local_id' => 141372,
                            'secret' => 4952891847968332097
                        ],

                        'photo_big' => [
                            '_' => 'fileLocation',
                            'dc_id' => 2,
                            'volume_id' => 225517222,
                            'local_id' => 141374,
                            'secret' => -5785720690880313215
                        ],

                    ],

                    'status' => $statusObj
                ]
            ]
        ]);
    }

}