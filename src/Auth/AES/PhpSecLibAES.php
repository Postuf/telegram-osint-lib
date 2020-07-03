<?php

namespace TelegramOSINT\Auth\AES;

use phpseclib\Crypt\AES as BaseAES;
use TelegramOSINT\Exception\TGException;

/**
 * AES IGE impl using phpseclib
 */
class PhpSecLibAES implements AES
{
    /**
     * @param string $message
     * @param string $key
     * @param string $iv
     *
     * @throws TGException
     *
     * @return string
     */
    public function encryptIgeMode(string $message, string $key, string $iv): string
    {
        return $this->ige($message, $key, $iv, true);
    }

    /**
     * @param string $message
     * @param string $key
     * @param string $iv
     *
     * @throws TGException
     *
     * @return string
     */
    public function decryptIgeMode(string $message, string $key, string $iv): string
    {
        return $this->ige($message, $key, $iv, false);
    }

    /**
     * @param string $message
     * @param string $key
     * @param string $iv
     * @param bool   $isEncrypt
     *
     * @throws TGException
     *
     * @return string
     */
    private function ige(string $message, string $key, string $iv, bool $isEncrypt): string
    {
        $cipher = new BaseAES(BaseAES::MODE_CBC);
        $cipher->setKey($key);
        $cipher->paddable = false;
        $blockSize = $cipher->block_size;

        if ((strlen($message) % $blockSize) !== 0) {
            throw new TGException(TGException::ERR_TL_ENCRYPTION_ERROR);
        }
        $ivBlockFirstPart = substr($iv, 0, $blockSize);
        $ivBlockSecondPart = substr($iv, $blockSize);
        $result = '';

        $messageLen = strlen($message);
        for($i = 0; $i < $messageLen; $i += $cipher->block_size) {
            $block = substr($message, $i, $blockSize);
            if ($isEncrypt) {
                $xoredBefore = $block ^ $ivBlockFirstPart;
                $encryptXored = $cipher->encrypt($xoredBefore);
                $xoredAfter = $encryptXored ^ $ivBlockSecondPart;
                $ivBlockFirstPart = $xoredAfter;
                $ivBlockSecondPart = $block;
            } else {
                $xoredBefore = $block ^ $ivBlockSecondPart;
                $decryptXored = $cipher->decrypt($xoredBefore);
                $xoredAfter = $decryptXored ^ $ivBlockFirstPart;
                $ivBlockFirstPart = $block;
                $ivBlockSecondPart = $xoredAfter;
            }
            $result .= $xoredAfter;
        }

        return $result;
    }
}
