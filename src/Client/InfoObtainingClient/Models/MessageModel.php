<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

class MessageModel
{
    /** @var int */
    private int $id;
    /** @var string */
    private string $text;
    /** @var int */
    private int $fromId;
    /** @var int */
    private int $date;

    public function __construct(
        int $id,
        string $text,
        int $fromId,
        int $date
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->fromId = $fromId;
        $this->date = $date;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFromId(): int
    {
        return $this->fromId;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
