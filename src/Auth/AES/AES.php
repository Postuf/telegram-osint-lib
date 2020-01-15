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
    /**
     * @param string $message
     * @param string $key
     * @param string $iv
     *
     * @return string
     */
    public function encryptIgeMode($message, $key, $iv);

    /**
     * @param string $message
     * @param string $key
     * @param string $iv
     *
     * @return string
     */
    public function decryptIgeMode($message, $key, $iv);
}
