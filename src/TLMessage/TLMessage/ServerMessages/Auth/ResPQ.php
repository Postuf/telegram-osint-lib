<?php

namespace TLMessage\TLMessage\ServerMessages\Auth;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;

class ResPQ extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'resPQ');
    }

    /**
     * @return string
     */
    public function getClientNonce()
    {
        return $this->getTlMessage()->getValue('nonce');
    }

    /**
     * @return string
     */
    public function getServerNonce()
    {
        return $this->getTlMessage()->getValue('server_nonce');
    }

    /**
     * @return int
     * @noinspection PhpUnused
     */
    public function getPQ()
    {
        $pqBin = $this->getTlMessage()->getValue('pq');

        return unpack('J', $pqBin)[1];
    }

    /**
     * @return array
     */
    public function getFingerprints()
    {
        return $this->getTlMessage()->getValue('server_public_key_fingerprints');
    }
}
