<?php

namespace TelegramOSINT\Auth\Certificate;

class Certificate
{
    /**
     * @var string
     */
    private $publicKey;
    /**
     * @var int
     */
    private $fingerPrint;

    /**
     * @param int    $fingerPrint
     * @param string $publicKey
     */
    public function __construct($fingerPrint, $publicKey)
    {
        $this->publicKey = $publicKey;
        $this->fingerPrint = $fingerPrint;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @return int
     */
    public function getFingerPrint()
    {
        return $this->fingerPrint;
    }

    /**
     * @param int $fingerPrint
     *
     * @return Certificate|null
     */
    public static function getCertificateByFingerPrint($fingerPrint)
    {
        foreach (CertificateStorage::getKnownCertificates() as $certificate)
            if($certificate->getFingerPrint() == $fingerPrint)
                return $certificate;

        return null;
    }
}
