<?php

namespace TelegramOSINT\Auth\Certificate;

class Certificate
{
    private string $publicKey;
    private int $fingerPrint;

    /**
     * @param int    $fingerPrint
     * @param string $publicKey
     */
    public function __construct(int $fingerPrint, string $publicKey)
    {
        $this->publicKey = $publicKey;
        $this->fingerPrint = $fingerPrint;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return int
     */
    public function getFingerPrint(): int
    {
        return $this->fingerPrint;
    }

    /**
     * @param int $fingerPrint
     *
     * @return Certificate|null
     */
    public static function getCertificateByFingerPrint(int $fingerPrint): ?self
    {
        foreach (CertificateStorage::getKnownCertificates() as $certificate) {
            if ($certificate->getFingerPrint() === $fingerPrint) {
                return $certificate;
            }
        }

        return null;
    }
}
