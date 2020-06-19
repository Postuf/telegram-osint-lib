<?php

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\StatusWatcherClient\ContactsKeeper;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TGConnection\SocketMessenger\SocketMessenger;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\delete_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\import_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\reset_saved_contacts;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class ContactsKeeperTest extends TestCase
{
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

    public function test_contacts_add(): void {
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
                    '_'        => 'contacts.importedContacts',
                    'imported' => [
                        [
                            '_'         => 'importedContact',
                            'user_id'   => 1,
                            'client_id' => 1123,
                        ],
                        [
                            '_'         => 'importedContact',
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
                    '_'     => 'contacts.contacts',
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
            ->method('getResponseAsync')
            ->willReturnCallback($responseCb);

        $returnedUsers = [];
        $this->keeper->getUserByPhone('123', static function ($user) use (&$returnedUsers) {
            if ($user) {
                $returnedUsers[] = $user;
            }
        });
        while ($calls) {
            foreach ($calls as $k => $call) {
                $callFunc = $call[0];
                $callFunc($call[1]);
                unset($calls[$k]);
            }
        }
        $this->assertCount(0, $returnedUsers);

        /* @noinspection PhpUnhandledExceptionInspection */
        $this->keeper->addNumbers($numbers, static function (ImportResult $result) use (&$importedPhones) {
            foreach ($result->importedPhones as $importedPhone) {
                $importedPhones[] = $importedPhone;
            }
        });

        while ($calls) {
            foreach ($calls as $k => $call) {
                $callFunc = $call[0];
                $callFunc($call[1]);
                unset($calls[$k]);
            }
        }

        $this->assertEquals($importedPhones, $numbers);
    }

    public function test_contacts_del(): void {
        $numbers = ['123'];
        $runCount = 0;
        $calls = [];
        $responseCb = static function (
            TLClientMessage $message,
            callable $onAsyncResponse
        ) use (&$runCount, &$calls) {
            if ($message instanceof delete_contacts) {
                $result = new AnonymousMessageMock([
                    '_'     => 'updates',
                    'users' => [
                        [
                            '_'     => 'user',
                            'id'    => 1,
                            'phone' => '123',
                        ],
                    ],
                    'retry_contacts' => [],
                ]);
            } elseif ($message instanceof get_contacts) {
                if ($runCount) {
                    $result = new AnonymousMessageMock([
                        '_'     => 'contacts.contacts',
                        'users' => [],
                    ]);
                } else {
                    $result = new AnonymousMessageMock([
                        '_'     => 'contacts.contacts',
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
            ->method('getResponseAsync')
            ->willReturnCallback($responseCb);

        $returnedUsers = [];
        $this->keeper->getUserByPhone('123', static function ($user) use (&$returnedUsers) {
            if ($user) {
                $returnedUsers[] = $user;
            }
        });
        while ($calls) {
            foreach ($calls as $k => $call) {
                $callFunc = $call[0];
                $callFunc($call[1]);
                unset($calls[$k]);
            }
        }
        $this->assertCount(1, $returnedUsers);

        while ($calls) {
            foreach ($calls as $k => $call) {
                $callFunc = $call[0];
                $callFunc($call[1]);
                unset($calls[$k]);
            }
        }

        $this->keeper->delNumbers($numbers, static function () { });
        while ($calls) {
            foreach ($calls as $k => $call) {
                $callFunc = $call[0];
                $callFunc($call[1]);
                unset($calls[$k]);
            }
        }

        $cc = [];
        $this->keeper->getCurrentContacts(static function (array $contacts) use (&$cc) {
            if ($contacts) {
                $cc[] = 1;
            }
        });

        while ($calls) {
            foreach ($calls as $k => $call) {
                $callFunc = $call[0];
                $callFunc($call[1]);
                unset($calls[$k]);
            }
        }

        $this->assertCount(0, $cc);
    }

    /**
     * @throws TGException
     */
    public function test_contacts_add_exception(): void {
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
                    '_'        => 'contacts.importedContacts',
                    'imported' => [
                        [
                            '_'         => 'importedContact',
                            'user_id'   => 1,
                            'client_id' => 1123,
                        ],
                        [
                            '_'         => 'importedContact',
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
                    '_'     => 'contacts.contacts',
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
            ->method('getResponseAsync')
            ->willReturnCallback($responseCb);

        /* @noinspection PhpUnhandledExceptionInspection */
        $this->keeper->addNumbers($numbers, static function (ImportResult $result) use (&$importedPhones) {
            foreach ($result->importedPhones as $importedPhone) {
                $importedPhones[] = $importedPhone;
            }
        });

        $this->expectException(TGException::class);

        while ($calls) {
            foreach ($calls as $k => $call) {
                $callFunc = $call[0];
                $callFunc($call[1]);
                unset($calls[$k]);
            }
        }
    }
}
