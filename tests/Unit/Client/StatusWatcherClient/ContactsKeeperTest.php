<?php

/** @noinspection UnusedFunctionResultInspection */

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\StatusWatcherClient\ContactsKeeper;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Exception\TimeWaitException;
use TelegramOSINT\TGConnection\SocketMessenger\SocketMessenger;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\add_contact;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\contacts_search;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\delete_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\import_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\reset_saved_contacts;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class ContactsKeeperTest extends TestCase
{
    /**
     * @see https://core.telegram.org/constructor/contacts.importedContacts
     */
    private const NODE_CONTACTS_IMPORTED_CONTACTS = 'contacts.importedContacts';
    /**
     * @see https://core.telegram.org/constructor/importedContact
     */
    private const NODE_IMPORTED_CONTACT = 'importedContact';
    /**
     * @see https://core.telegram.org/constructor/contacts.contacts
     */
    private const NODE_CONTACTS_CONTACTS = 'contacts.contacts';
    /**
     * @see https://core.telegram.org/constructor/contacts.found
     */
    private const NODE_CONTACTS_FOUND = 'contacts.found';
    /**
     * @see https://core.telegram.org/constructor/updates
     */
    private const NODE_UPDATES = 'updates';
    private const METHOD_GET_RESPONSE_ASYNC = 'getResponseAsync';

    /** @var SocketMessenger|MockObject */
    private $socketMessengerMock;
    /** @var ContactsKeeper */
    private $keeper;

    protected function setUp(): void
    {
        parent::setUp();

        $basicClientMock = $this->createMock(BasicClient::class);
        $this->socketMessengerMock = $this->createMock(SocketMessenger::class);
        $basicClientMock
            ->method('getConnection')
            ->willReturn($this->socketMessengerMock);
        $this->keeper = new ContactsKeeper($basicClientMock);
    }

    /**
     * Check that contacts are added by phone
     *
     * @throws TGException
     */
    public function test_contacts_add(): void
    {
        $numbers = ['123', '456'];
        $importedPhones = [];
        $runCount = 0;
        $calls = [];
        $responseCb = static function (
            TLClientMessage $message,
            callable $onAsyncResponse
        ) use (&$runCount, &$calls) {
            if ($message instanceof import_contacts) {
                $runCount++;
                $result = new AnonymousMessageMock([
                    '_'        => self::NODE_CONTACTS_IMPORTED_CONTACTS,
                    'imported' => [
                        [
                            '_'         => self::NODE_IMPORTED_CONTACT,
                            'user_id'   => 1,
                            'client_id' => 1123,
                        ],
                        [
                            '_'         => self::NODE_IMPORTED_CONTACT,
                            'user_id'   => 2,
                            'client_id' => 1456,
                        ],
                    ],
                    'users' => [
                        [
                            '_'     => 'user',
                            'id'    => 1,
                            'phone' => '123',
                        ],
                        [
                            '_'     => 'user',
                            'id'    => 2,
                            'phone' => '456',
                        ],
                    ],
                    'retry_contacts' => [],
                ]);
            } elseif ($message instanceof get_contacts) {
                $result = new AnonymousMessageMock([
                    '_'     => self::NODE_CONTACTS_CONTACTS,
                    'users' => [
                    ],
                ]);
            } else {
                $result = new AnonymousMessageMock([
                    '_' => 'error',
                ]);
            }
            $calls[] = [$onAsyncResponse, $result];
        };
        $this->socketMessengerMock
            ->method(self::METHOD_GET_RESPONSE_ASYNC)
            ->willReturnCallback($responseCb);

        $returnedUsers = [];
        $this->keeper->getUserByPhone('123', static function ($user) use (&$returnedUsers) {
            if ($user) {
                $returnedUsers[] = $user;
            }
        });
        $this->processCalls($calls);
        self::assertCount(0, $returnedUsers);

        $this->keeper->addNumbers($numbers, static function (ImportResult $result) use (&$importedPhones) {
            foreach ($result->importedPhones as $importedPhone) {
                $importedPhones[] = $importedPhone;
            }
        });

        $this->processCalls($calls);

        self::assertEquals($importedPhones, $numbers);
    }

    /**
     * Check that contact is added by username
     */
    public function test_contacts_add_username(): void
    {
        $runCount = 0;
        $calls = [];
        $responseCb = static function (
            TLClientMessage $message,
            callable $onAsyncResponse
        ) use (&$runCount, &$calls) {
            if ($message instanceof contacts_search) {
                $runCount++;
                $result = new AnonymousMessageMock([
                    '_'     => self::NODE_CONTACTS_FOUND,
                    'users' => [
                        [
                            '_'           => 'user',
                            'id'          => 1,
                            'access_hash' => 2135235215,
                            'phone'       => '123',
                            'username'    => 'aaa',
                        ],
                    ],
                ]);
            } elseif ($message instanceof get_contacts) {
                $result = new AnonymousMessageMock([
                    '_'     => self::NODE_CONTACTS_CONTACTS,
                    'users' => [
                    ],
                ]);
            } elseif ($message instanceof add_contact) {
                $result = new AnonymousMessageMock([
                    '_'     => self::NODE_UPDATES,
                    'users' => [
                        [
                            '_'        => 'user',
                            'id'       => 1,
                            'username' => 'aaa',
                        ],
                    ],
                ]);
            } else {
                $result = new AnonymousMessageMock([
                    '_' => 'error',
                ]);
            }
            $calls[] = [$onAsyncResponse, $result];
        };
        $this->socketMessengerMock
            ->method(self::METHOD_GET_RESPONSE_ASYNC)
            ->willReturnCallback($responseCb);

        $this->keeper->addUser('aaa', function (bool $added) {
            $this->assertTrue($added);
        });
        $this->processCalls($calls);
        self::assertCount(1, $this->keeper->getContacts());

        $this->processCalls($calls);
    }

    /**
     * Check that contact can be deleted by phone
     */
    public function test_contacts_del(): void
    {
        $numbers = ['123'];
        $runCount = 0;
        $calls = [];
        $responseCb = static function (
            TLClientMessage $message,
            callable $onAsyncResponse
        ) use (&$runCount, &$calls) {
            if ($message instanceof delete_contacts) {
                $result = new AnonymousMessageMock([
                    '_'     => self::NODE_UPDATES,
                    'users' => [
                        [
                            '_'  => 'user',
                            'id' => 1,
                        ],
                    ],
                ]);
            } elseif ($message instanceof get_contacts) {
                if ($runCount) {
                    $result = new AnonymousMessageMock([
                        '_'     => self::NODE_CONTACTS_CONTACTS,
                        'users' => [],
                    ]);
                } else {
                    $result = new AnonymousMessageMock([
                        '_'     => self::NODE_CONTACTS_CONTACTS,
                        'users' => [
                            [
                                '_'           => 'user',
                                'id'          => 1,
                                'phone'       => '123',
                                'access_hash' => 1,
                            ],
                        ],
                    ]);
                }
                $runCount++;
            } elseif ($message instanceof reset_saved_contacts) {
                $result = new AnonymousMessageMock([
                    '_' => 'success',
                ]);
            } else {
                $result = new AnonymousMessageMock([
                    '_' => 'error',
                ]);
            }
            $calls[] = [$onAsyncResponse, $result];
        };
        $this->socketMessengerMock
            ->method(self::METHOD_GET_RESPONSE_ASYNC)
            ->willReturnCallback($responseCb);

        $returnedUsers = [];
        $this->keeper->getUserByPhone('123', static function ($user) use (&$returnedUsers) {
            if ($user) {
                $returnedUsers[] = $user;
            }
        });
        $this->processCalls($calls);
        self::assertCount(1, $returnedUsers);

        $this->processCalls($calls);

        $this->keeper->delNumbers($numbers, static function () {
        });
        $this->processCalls($calls);

        $cc = [];
        $this->keeper->getCurrentContacts(static function (array $contacts) use (&$cc) {
            if ($contacts) {
                $cc[] = 1;
            }
        });

        $this->processCalls($calls);

        self::assertCount(0, $cc);
    }

    /**
     * Check that contact can be deleted by username
     */
    public function test_contacts_del_username(): void
    {
        $runCount = 0;
        $calls = [];
        $calls = $this->prepareResponseWithOneUserForDel($runCount, $calls);

        $returnedUsers = [];
        $this->keeper->getUserByPhone('123', static function ($user) use (&$returnedUsers) {
            if ($user) {
                $returnedUsers[] = $user;
            }
        });
        $this->processCalls($calls);
        self::assertCount(1, $returnedUsers);

        $this->processCalls($calls);

        // should be ok even if username not exists
        $this->keeper->delUsers(['aaa', 'bbb'], static function () {
        });
        $this->processCalls($calls);

        $cc = [];
        $this->keeper->getCurrentContacts(static function (array $contacts) use (&$cc) {
            if ($contacts) {
                $cc[] = 1;
            }
        });

        $this->processCalls($calls);

        self::assertCount(0, $cc);
    }

    /**
     * Check that contact can be deleted by new phone after phone update
     */
    public function test_contacts_update_phone(): void
    {
        $runCount = 0;
        $calls = [];
        $calls = $this->prepareResponseWithOneUserForDel($runCount, $calls);

        $returnedUsers = [];
        $this->keeper->getUserByPhone('123', static function ($user) use (&$returnedUsers) {
            if ($user) {
                $returnedUsers[] = $user;
            }
        });
        $this->processCalls($calls);
        self::assertCount(1, $returnedUsers);

        $this->processCalls($calls);

        $this->keeper->updatePhone(1, '124');

        $this->keeper->delNumbers(['124'], static function () {
        });
        $this->processCalls($calls);

        $cc = [];
        $this->keeper->getCurrentContacts(static function (array $contacts) use (&$cc) {
            if ($contacts) {
                $cc[] = 1;
            }
        });

        $this->processCalls($calls);

        self::assertCount(0, $cc);
    }

    /**
     * Check that contact can be deleted by new username after username update
     */
    public function test_contacts_update_username(): void
    {
        $runCount = 0;
        $calls = [];
        $calls = $this->prepareResponseWithOneUserForDel($runCount, $calls);

        $returnedUsers = [];
        $this->keeper->getUserByPhone('123', static function ($user) use (&$returnedUsers) {
            if ($user) {
                $returnedUsers[] = $user;
            }
        });
        $this->processCalls($calls);
        self::assertCount(1, $returnedUsers);

        $this->processCalls($calls);

        $this->keeper->updateUsername(1, 'bbb');

        $this->keeper->delUsers(['bbb'], static function () {
        });
        $this->processCalls($calls);

        $cc = [];
        $this->keeper->getCurrentContacts(static function (array $contacts) use (&$cc) {
            if ($contacts) {
                $cc[] = 1;
            }
        });

        $this->processCalls($calls);

        self::assertCount(0, $cc);
    }

    /**
     * Check that deleting contact both by phone number and username deletes it once
     */
    public function test_contacts_del_username_and_number(): void
    {
        $runCount = 0;
        $calls = [];
        $responseCb = function (
            TLClientMessage $message,
            callable $onAsyncResponse
        ) use (&$runCount, &$calls) {
            if ($message instanceof delete_contacts) {
                // expect contacts to be merged before del
                $this->assertCount(1, $message->getContactsToDelete());
                $result = new AnonymousMessageMock([
                    '_'     => self::NODE_UPDATES,
                    'users' => [
                        [
                            '_'  => 'user',
                            'id' => 1,
                        ],
                    ],
                ]);
            } elseif ($message instanceof get_contacts) {
                if ($runCount) {
                    $result = new AnonymousMessageMock([
                        '_'     => self::NODE_CONTACTS_CONTACTS,
                        'users' => [],
                    ]);
                } else {
                    $result = new AnonymousMessageMock([
                        '_'     => self::NODE_CONTACTS_CONTACTS,
                        'users' => [
                            [
                                '_'           => 'user',
                                'id'          => 1,
                                'phone'       => '123',
                                'access_hash' => 1,
                                'username'    => 'aaa',
                            ],
                            [
                                '_'           => 'user',
                                'id'          => 2,
                                'phone'       => '124',
                                'access_hash' => 3,
                                'username'    => 'aaabb',
                            ],
                        ],
                    ]);
                }
                $runCount++;
            } elseif ($message instanceof reset_saved_contacts) {
                $result = new AnonymousMessageMock([
                    '_' => 'success',
                ]);
            } else {
                $result = new AnonymousMessageMock([
                    '_' => 'error',
                ]);
            }
            $calls[] = [$onAsyncResponse, $result];
        };
        $this->socketMessengerMock
            ->method(self::METHOD_GET_RESPONSE_ASYNC)
            ->willReturnCallback($responseCb);

        $returnedUsers = [];
        $this->keeper->getUserByPhone('123', static function ($user) use (&$returnedUsers) {
            if ($user) {
                $returnedUsers[] = $user;
            }
        });
        $this->processCalls($calls);
        self::assertCount(1, $returnedUsers);

        $this->processCalls($calls);

        // should be ok even if username not exists
        $this->keeper->delNumbersAndUsers(['123'], ['aaa'], static function () {
        });
        $this->processCalls($calls);

        $cc = [];
        $this->keeper->getCurrentContacts(static function (array $contacts) use (&$cc) {
            if ($contacts) {
                $cc[] = 1;
            }
        });

        $this->processCalls($calls);

        self::assertCount(1, $cc);
    }

    /**
     * Check that deleting contacts in several consequent calls leads to exception
     */
    public function test_contacts_del_frequent(): void
    {
        $runCount = 0;
        $calls = [];
        $calls = $this->prepareResponseWithOneUserForDel($runCount, $calls);

        $returnedUsers = [];
        $this->keeper->getUserByPhone('123', static function ($user) use (&$returnedUsers) {
            if ($user) {
                $returnedUsers[] = $user;
            }
        });
        $this->processCalls($calls);
        self::assertCount(1, $returnedUsers);

        $this->processCalls($calls);

        $this->keeper->delUsers(['aaa'], static function () {
        });
        $this->expectException(TimeWaitException::class);
        $this->keeper->delUsers(['aaa'], static function () {
        });
    }

    /**
     * Check that adding same contact twice leads to exception
     *
     * @throws TGException
     */
    public function test_contacts_add_exception(): void
    {
        $numbers = ['123', '456'];
        $importedPhones = [];
        $runCount = 0;
        $calls = [];
        $responseCb = static function (
            TLClientMessage $message,
            callable $onAsyncResponse
        ) use (&$runCount, &$calls) {
            if ($message instanceof import_contacts) {
                $runCount++;
                $result = new AnonymousMessageMock([
                    '_'        => self::NODE_CONTACTS_IMPORTED_CONTACTS,
                    'imported' => [
                        [
                            '_'         => self::NODE_IMPORTED_CONTACT,
                            'user_id'   => 1,
                            'client_id' => 1123,
                        ],
                        [
                            '_'         => self::NODE_IMPORTED_CONTACT,
                            'user_id'   => 2,
                            'client_id' => 1456,
                        ],
                    ],
                    'users' => [
                        [
                            '_'     => 'user',
                            'id'    => 1,
                            'phone' => '123',
                        ],
                        [
                            '_'     => 'user',
                            'id'    => 2,
                            'phone' => '456',
                        ],
                    ],
                    'retry_contacts' => [],
                ]);
            } elseif ($message instanceof get_contacts) {
                $result = new AnonymousMessageMock([
                    '_'     => self::NODE_CONTACTS_CONTACTS,
                    'users' => [
                        [
                            '_'     => 'user',
                            'id'    => 1,
                            'phone' => '123',
                        ],
                    ],
                ]);
            } else {
                $result = new AnonymousMessageMock([
                    '_' => 'error',
                ]);
            }
            $calls[] = [$onAsyncResponse, $result];
        };
        $this->socketMessengerMock
            ->method(self::METHOD_GET_RESPONSE_ASYNC)
            ->willReturnCallback($responseCb);

        $this->keeper->addNumbers($numbers, static function (ImportResult $result) use (&$importedPhones) {
            foreach ($result->importedPhones as $importedPhone) {
                $importedPhones[] = $importedPhone;
            }
        });

        $this->expectException(TGException::class);

        $this->processCalls($calls);
    }

    /**
     * @param array $calls
     */
    private function processCalls(array &$calls): void
    {
        while ($calls) {
            foreach ($calls as $k => $call) {
                $callFunc = $call[0];
                $callFunc($call[1]);
                unset($calls[$k]);
            }
        }
    }

    /**
     * @param int   $runCount
     * @param array $calls
     *
     * @return array
     */
    private function prepareResponseWithOneUserForDel(int &$runCount, array &$calls): array
    {
        $responseCb = static function (
            TLClientMessage $message,
            callable $onAsyncResponse
        ) use (&$runCount, &$calls) {
            if ($message instanceof delete_contacts) {
                $result = new AnonymousMessageMock([
                    '_'     => self::NODE_UPDATES,
                    'users' => [
                        [
                            '_'  => 'user',
                            'id' => 1,
                        ],
                    ],
                ]);
            } elseif ($message instanceof get_contacts) {
                if ($runCount) {
                    $result = new AnonymousMessageMock([
                        '_'     => self::NODE_CONTACTS_CONTACTS,
                        'users' => [],
                    ]);
                } else {
                    $result = new AnonymousMessageMock([
                        '_'     => self::NODE_CONTACTS_CONTACTS,
                        'users' => [
                            [
                                '_'           => 'user',
                                'id'          => 1,
                                'phone'       => '123',
                                'access_hash' => 1,
                                'username'    => 'aaa',
                            ],
                        ],
                    ]);
                }
                $runCount++;
            } elseif ($message instanceof reset_saved_contacts) {
                $result = new AnonymousMessageMock([
                    '_' => 'success',
                ]);
            } else {
                $result = new AnonymousMessageMock([
                    '_' => 'error',
                ]);
            }
            $calls[] = [$onAsyncResponse, $result];
        };
        $this->socketMessengerMock
            ->method(self::METHOD_GET_RESPONSE_ASYNC)
            ->willReturnCallback($responseCb);

        return $calls;
    }
}
