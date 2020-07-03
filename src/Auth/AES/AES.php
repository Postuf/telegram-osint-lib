<?php

namespace TelegramOSINT\Auth\AES;

/**
 * Interface AES
 *
 * @see https://core.telegram.org/techfaq#q-do-you-use-ige-ige-is-broken
 * @see https://mgp25.com/AESIGE/
 */
interface AES
{
    public function encryptIgeMode(string $message, string $key, string $iv): string;

    public function decryptIgeMode(string $message, string $key, string $iv): string;
}
