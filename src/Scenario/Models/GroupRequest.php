<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario\Models;

use InvalidArgumentException;

class GroupRequest
{
    /** @var int|null */
    private $groupId;
    /** @var string|null */
    private $username;

    /**
     * @param int|null    $groupId
     * @param string|null $username any format (groupname, https://t.me/groupname, `@groupname`)
     */
    public function __construct(?int $groupId = null, ?string $username = null)
    {
        if (!$groupId && !$username) {
            throw new InvalidArgumentException('Invalid groupId or username');
        }

        $this->groupId = $groupId;
        if ($username) {
            if (strpos($username, '/') !== false) {
                $parts = explode('/', $username);
                $username = $parts[count($parts) - 1];
            }

            $username = str_replace('@', '', $username);

            $this->username = $username;
        }
    }

    public static function ofUserName(string $username): self
    {
        return new self(null, $username);
    }

    public static function ofGroupId(int $groupId): self
    {
        return new self($groupId);
    }

    public function getUserName(): ?string
    {
        return $this->username;
    }

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function __toString()
    {
        return $this->username ? 'username: '.$this->username : 'id: '.$this->groupId;
    }
}
