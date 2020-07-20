<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\Models\StickerSetModel;
use TelegramOSINT\Client\InfoObtainingClient\StickerClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Stickers\StickerSet;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Stickers\StickerSetCoveredBase;
use TelegramOSINT\Tools\Proxy;

/**
 * Get featured stickers
 */
class FeaturedStickerSetScenario extends InfoClientScenario
{
    /** @var callable */
    protected $setFunc;

    /**
     * @param callable                      $stickerSetFunc  function(StickerSetModel $model)
     * @param ClientGeneratorInterface|null $clientGenerator
     * @param Proxy|null                    $proxy
     *
     * @throws TGException
     */
    public function __construct(
        callable $stickerSetFunc,
        ClientGeneratorInterface $clientGenerator = null,
        ?Proxy $proxy = null
    ) {
        if (!$clientGenerator) {
            $clientGenerator = new StickerClientGenerator();
        }
        $this->setFunc = $stickerSetFunc;
        parent::__construct($clientGenerator, $proxy);
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->authAndPerformActions(function () {
            /** @var StickerClient $infoClient */
            $infoClient = $this->infoClient;
            $infoClient->getFeaturedStickers(function (array $stickerSets) {
                $count = count($stickerSets);
                foreach ($stickerSets as $stickerSet) {
                    /** @var StickerSetCoveredBase $sticketSet */
                    /** @var StickerSet $set */
                    $set = $stickerSet->getSet();
                    $fn = $this->setFunc;
                    $fn(StickerSetModel::of($set));
                }
            });
        }, $pollAndTerminate);
    }
}
