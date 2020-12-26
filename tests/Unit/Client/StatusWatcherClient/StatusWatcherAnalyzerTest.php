<?php

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherAnalyzer;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacksMiddleware;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;

class StatusWatcherAnalyzerTest extends TestCase
{
    /** @var StatusWatcherAnalyzer */
    private StatusWatcherAnalyzer $analyzer;
    /** @var MockObject|StatusWatcherCallbacksMiddleware */
    private $callbacks;

    /**
     * @throws TGException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->callbacks = $this->createMock(StatusWatcherCallbacksMiddleware::class);
        $this->analyzer = new StatusWatcherAnalyzer($this->callbacks);
    }

    /**
     * We process single username change correctly
     *
     * @throws TGException
     */
    public function test_analyze_username_change_single(): void
    {
        $this->callbacks
            ->expects(self::once())
            ->method('onUserNameChange')
            ->with(1, 'yyy');
        $this->analyzer->analyzeMessage(new AnonymousMessageMock([
            '_'      => 'updateShort',
            'update' => $this->getSingleUsernameUpdate(),
            'date'   => 0,
        ]));
    }

    /**
     * We process username change among other updates correctly
     *
     * @throws TGException
     */
    public function test_analyze_username_change_multiple(): void
    {
        $this->callbacks
            ->expects(self::once())
            ->method('onUserNameChange')
            ->with(1, 'yyy');
        $this->analyzer->analyzeMessage(new AnonymousMessageMock([
            '_'       => 'updates',
            'updates' => [
                $this->getSingleUsernameUpdate(),
            ],
            'users' => [],
            'chats' => [],
            'seq'   => 0,
            'date'  => 0,
        ]));
    }

    /**
     * @return array
     */
    private function getSingleUsernameUpdate(): array
    {
        return [
            '_'          => 'updateUserName',
            'user_id'    => 1,
            'first_name' => '',
            'last_name'  => '',
            'username'   => 'yyy',
        ];
    }
}
