<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\Models\GeoChannelModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\Updates;

class GeoSearchScenario extends InfoClientScenario
{
    private const DECIMALS = 6;

    /** @var array */
    private $points;
    /** @var callable|null */
    private $onChatReady;
    /** @var int */
    private $limit;
    /** @var int */
    private $counter;

    /**
     * @param array                         $points
     * @param callable|null                 $onReady         function(GeoChannelModel $model)
     * @param ClientGeneratorInterface|null $clientGenerator
     * @param int                           $limit
     *
     * @throws TGException
     */
    public function __construct(
        array $points,
        callable $onReady = null,
        ClientGeneratorInterface $clientGenerator = null,
        int $limit = 100
    ) {
        parent::__construct($clientGenerator);
        $this->points = $points;
        $this->onChatReady = $onReady;
        $this->limit = $limit;
        $this->counter = 0;
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $actions = function (): void {
            $lastCb = null;
            foreach ($this->points as $point) {
                [$lat, $lon] = $point;
                if ($this->counter > $this->limit) {
                    break;
                }
                $lastCb = function () use ($lat, $lon, $lastCb) {
                    $onComplete = function (AnonymousMessage $message) use ($lat, $lon, $lastCb) {
                        /** @see https://core.telegram.org/constructor/updates */
                        if (!Updates::isIt($message)) {
                            return;
                        }

                        foreach ((new Updates($message))->getChats() as $chat) {
                            $this->counter++;
                            if ($this->counter > $this->limit) {
                                break;
                            }
                            $latF = number_format($lat, self::DECIMALS);
                            $lonF = number_format($lon, self::DECIMALS);
                            Logger::log(__CLASS__, "found group '{$chat->title}' near ($latF, $lonF)");
                            $chatModel = GeoChannelModel::of($chat, $lat, $lon);
                            if ($this->onChatReady) {
                                $onChatReady = $this->onChatReady;
                                $onChatReady($chatModel);
                            }
                        }

                        usleep(700 * 1000);
                        if ($lastCb) {
                            $lastCb();
                        }
                    };
                    $this->infoClient->getLocated($lat, $lon, $onComplete);
                };
            }

            if ($lastCb) {
                $lastCb();
            }
        };

        $this->authAndPerformActions($actions, $pollAndTerminate);
    }
}
