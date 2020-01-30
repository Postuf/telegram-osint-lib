<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

class GeoChannelModel extends ChannelModel
{
    /** @var float */
    public $lat;
    /** @var float */
    public $lon;

    public function __construct(int $id, int $accessHash, string $title, float $lat, float $lon)
    {
        parent::__construct($id, $accessHash, $title);
        $this->lat = $lat;
        $this->lon = $lon;
    }

    public static function of(ChannelModel $model, float $lat, float $lon): self {
        return new self(
            $model->id,
            $model->accessHash,
            $model->title,
            $lat,
            $lon
        );
    }
}
