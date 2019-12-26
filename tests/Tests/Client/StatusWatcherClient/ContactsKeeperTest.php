<?php

namespace Tests\Client\StatusWatcherClient;

use Client\BasicClient\BasicClient;
use Client\StatusWatcherClient\ContactsKeeper;
use Client\StatusWatcherClient\Models\ImportResult;
use Exception\TGException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Tests\Client\StatusWatcherClient\AnonymousMessageMock;
use TGConnection\SocketMessenger\SocketMessenger;
use TLMessage\TLMessage\ClientMessages\Shared\delete_contacts;
use TLMessage\TLMessage\ClientMessages\TgApp\get_contacts;
use TLMessage\TLMessage\ClientMessages\TgApp\import_contacts;
use TLMessage\TLMessage\ClientMessages\TgApp\reset_saved_contacts;
use TLMessage\TLMessage\TLClientMessage;

class ContactsKeeperTest extends TestCase
{
    /** @var BasicClient|MockObject $basicClientMock */
    private $basicClientMock;
    /** @var SocketMessenger|MockObject $socketMessengerMock */
    private $socketMessengerMock;
    /** @var ContactsKeeper */
    private $keeper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basicClientMock = $this->createMock(BasicClient::class);
        $this->socketMessengerMock = $this->createMock(SocketMessenger::class);
        $this->basicClientMock
            ->method('getConnection')
            ->willReturn($this->socketMessengerMock);
        $this->keeper = new ContactsKeeper($this->basicClientMock);
    }

    public function test_contacts_add() {
        $numbers = ['123', '456'];
        $importedPhones = [];
        $runCount = 0;
        $calls = [];
        $responseCb = function (
            TLClientMessage $message,
            callable $onAsyncResponse
        ) use ($numbers, &$runCount, &$calls) {
            if ($message instanceof import_contacts) {
                $runCount++;
                $result = new AnonymousMessageMock([
                    '_'        => 'contacts.importedContacts',
                    'imported' => [
                        [
                            '_'         => 'importedContact',
                            'user_id'   => 1,
                            'client_id' => '1123',
                        ],
                        [
                            '_'         => 'importedContact',
                            'user_id'   => 2,
                            'client_id' => '1456',
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
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->keeper->getUserByPhone('123', function ($user) use (&$returnedUsers) {
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
        $this->assertEquals(count($returnedUsers), 0);

        /* @noinspection PhpUnhandledExceptionInspection */
        $this->keeper->addNumbers($numbers, function (ImportResult $result) use (&$importedPhones) {
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

    public function test_contacts_del() {
        $numbers = ['123'];
        $runCount = 0;
        $calls = [];
        $responseCb = function (
            TLClientMessage $message,
            callable $onAsyncResponse
        ) use ($numbers, &$runCount, &$calls) {
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
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->keeper->getUserByPhone('123', function ($user) use (&$returnedUsers) {
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
        $this->assertEquals(count($returnedUsers), 1);

        while ($calls) {
            foreach ($calls as $k => $call) {
                $callFunc = $call[0];
                $callFunc($call[1]);
                unset($calls[$k]);
            }
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        $this->keeper->delNumbers($numbers, function () { });
        while ($calls) {
            foreach ($calls as $k => $call) {
                $callFunc = $call[0];
                $callFunc($call[1]);
                unset($calls[$k]);
            }
        }

        $cc = [];
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->keeper->getCurrentContacts(function (array $contacts) use (&$cc) {
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

        $this->assertEquals(count($cc), 0);
    }

    /**
     * @throws TGException
     */
    public function test_contacts_add_exception() {
        $numbers = ['123', '456'];
        $importedPhones = [];
        $runCount = 0;
        $calls = [];
        $responseCb = function (
            TLClientMessage $message,
            callable $onAsyncResponse
        ) use ($numbers, &$runCount, &$calls) {
            if ($message instanceof import_contacts) {
                $runCount++;
                $result = new AnonymousMessageMock([
                    '_'        => 'contacts.importedContacts',
                    'imported' => [
                        [
                            '_'         => 'importedContact',
                            'user_id'   => 1,
                            'client_id' => '1123',
                        ],
                        [
                            '_'         => 'importedContact',
                            'user_id'   => 2,
                            'client_id' => '1456',
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
        $this->keeper->addNumbers($numbers, function (ImportResult $result) use (&$importedPhones) {
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
