<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\Models\StickerSetModel;
use TelegramOSINT\Client\InfoObtainingClient\StickerClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Scenario\Models\StickerSetId;
use TelegramOSINT\Tools\Proxy;

class GetStickerSetScenario extends FeaturedStickerSetScenario
{
    /** @var StickerSetId */
    private StickerSetId $stickerId;
    /** @var bool */
    private bool $standAlone;

    /**
     * @param StickerSetId                  $id
     * @param callable                      $stickerSetFunc  function(StickerSetModel $model)
     * @param bool                          $standAlone
     * @param ClientGeneratorInterface|null $clientGenerator
     * @param Proxy|null                    $proxy
     *
     * @throws TGException
     */
    public function __construct(
        StickerSetId $id,
        callable $stickerSetFunc,
        bool $standAlone = false,
        ClientGeneratorInterface $clientGenerator = null,
        ?Proxy $proxy = null
    ) {
        $this->stickerId = $id;
        $this->standAlone = $standAlone;
        parent::__construct($stickerSetFunc, $clientGenerator, $proxy);
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $fn = function () {
            /** @var StickerClient $infoClient */
            $infoClient = $this->infoClient;
            $infoClient->getStickerSet(
                $this->stickerId->getId(),
                $this->stickerId->getAccessHash(),
                function (StickerSetModel $model) {
                    $fn = $this->setFunc;
                    $fn($model);
                }
            );
        };
        if ($this->standAlone) {
            $this->authAndPerformActions($fn, $pollAndTerminate);
        } else {
            $fn();
            if ($pollAndTerminate) {
                $this->pollAndTerminate();
            }
        }
    }
}
