<?php

declare(strict_types=1);

namespace Unit\Client;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\MultiClient;
use TelegramOSINT\Exception\TGException;

class MultiClientTest extends TestCase
{
    /**
     * @test
     *
     * @throws TGException
     */
    public function it_logs_in(): void
    {
        $mc = $this->createClient(function (InfoClient $client) {
            /** @var InfoClient|MockObject $client */
            $client->expects($this->once())->method('login');
        });
        $mc->connect();
    }

    /**
     * @test
     *
     * @throws TGException
     */
    public function it_polls(): void
    {
        $mc = $this->createClient(function (InfoClient $client) {
            /** @var InfoClient|MockObject $client */
            $client->expects($this->once())->method('pollMessage');
        });
        $mc->poll();
    }

    /**
     * @param callable $require function(InfoClient $client)
     *
     * @throws TGException
     *
     * @return MultiClient
     */
    protected function createClient(callable $require): MultiClient
    {
        $k1 = '79000000000:7b22646576696365223a2273616d73756e67534d2d473936305531222c22616e64726f696453646b56657273696f6e223a2253444b203237222c2266697273744e616d65223a2250656172736f6e222c226c6173744e616d65223a22547365222c226465766963654c616e67223a22656e2d7573222c226170704c616e67223a22656e222c2261707056657273696f6e223a22352e31312e30222c2261707056657273696f6e436f6465223a223137313037222c226c6179657256657273696f6e223a3130357d:hYMe8w6HXp/W/fSaeX4/k/9XcWMUHPws3E8FV6RZUHnHbWDwOHkTte5oLA4K/MQ11zrFNWdrc65ECrjIrfocKKrEnbav6SfATcipepUpkNK0dMcUc9GhTVU+m6w1c4pwR15pHI8AJYAhfQaC+/P1DpZSBJpY5m50r3T+EyY8A+P2ylxNSpOcaRgSsfFKxqbA+O37kkVFj68jC9iA3Umq69Xpy18ji3tmEuwnL97Z6pS9fkzy1GlTnbPn8g0YQ94E5c0yazWYxp8ugZb/CtQMdHwd/5BnFhJpXwm+XG2q74iPMfjVJopY0KF/smLf4eXR+ZfetAhwb+y0rJEaup3k3g==:7b2263726561746564223a313537313733373239352c226170695f6964223a362c2264635f6964223a322c2264635f6970223a223134392e3135342e3136372e3530222c2264635f706f7274223a3434337d';

        return new MultiClient([$k1], function () use ($require) {
            $mockClient = $this->createMock(InfoClient::class);
            $mockClient
                ->method('isLoggedIn')
                ->willReturn(true);
            $require($mockClient);

            return $mockClient;
        });
    }
}
