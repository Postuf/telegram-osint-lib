<?php

namespace TelegramOSINT\Auth\Protocol;

use TelegramOSINT\Auth\AES\AES;
use TelegramOSINT\Auth\AES\PhpSecLibAES;
use TelegramOSINT\Auth\Authorization;
use TelegramOSINT\Auth\AuthParams;
use TelegramOSINT\Auth\Certificate\Certificate;
use TelegramOSINT\Auth\Factorization\GmpFactorizer;
use TelegramOSINT\Auth\Factorization\PQ;
use TelegramOSINT\Auth\PowMod\PhpSecLibPowMod;
use TelegramOSINT\Auth\PowMod\PowMod;
use TelegramOSINT\Auth\RSA\PhpSecLibRSA;
use TelegramOSINT\Auth\RSA\RSA;
use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\MTSerialization\OwnImplementation\OwnDeserializer;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TGConnection\Socket\NonBlockingProxySocket;
use TelegramOSINT\TGConnection\Socket\TcpSocket;
use TelegramOSINT\TGConnection\SocketMessenger\NotEncryptedSocketMessenger;
use TelegramOSINT\TGConnection\SocketMessenger\SocketMessenger;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\client_dh_inner_data;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\req_dh_params;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\req_pq_multi;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\set_client_dh_params;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Auth\DHGenOk;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Auth\DHReq;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Auth\DHServerInnerData;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Auth\ResPQ;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;
use TelegramOSINT\Tools\Proxy;

abstract class BaseAuthorization implements Authorization
{
    private DataCentre $dc;
    /**
     * @var SocketMessenger
     */
    private $socketContainer;
    /**
     * @var string
     */
    private $oldClientNonce;
    /**
     * @var string
     */
    private $newClientNonce;
    private string $obtainedServerNonce;
    /**
     * @var RSA
     */
    private $rsa;
    /**
     * @var AES
     */
    private $aes;
    /**
     * @var PowMod
     */
    private $powMod;
    private string $tmpAesKey;
    private string $tmpAesIV;

    /**
     * @param DataCentre $dc DC AuthKey must be generated on
     * @param Proxy|null $proxy
     *
     * @param ClientDebugLogger|null $logger
     * @throws TGException
     */
    public function __construct(DataCentre $dc, ?Proxy $proxy = null, ?ClientDebugLogger $logger = null)
    {
        $cb = static function () {
        };
        $socket = $proxy
            ? new NonBlockingProxySocket($proxy, $dc, $cb)
            : new TcpSocket($dc, $cb);

        $this->dc = $dc;
        $this->socketContainer = new NotEncryptedSocketMessenger($socket, $logger);

        $this->rsa = new PhpSecLibRSA();
        $this->aes = new PhpSecLibAES();
        $this->powMod = new PhpSecLibPowMod();

        /** @noinspection CryptographicallySecureRandomnessInspection */
        $this->oldClientNonce = openssl_random_pseudo_bytes(16, $strong);
        if ($strong === false || $this->oldClientNonce === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }
        /** @noinspection CryptographicallySecureRandomnessInspection */
        $this->newClientNonce = openssl_random_pseudo_bytes(32, $strong);
        if ($strong === false || $this->newClientNonce === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }
    }

    /**
     * @param int    $pq
     * @param int    $p
     * @param int    $q
     * @param string $oldClientNonce
     * @param string $serverNonce
     * @param string $newClientNonce
     *
     * @return TLClientMessage
     */
    abstract protected function getPqInnerDataMessage($pq, $p, $q, $oldClientNonce, $serverNonce, $newClientNonce): TLClientMessage;

    /**
     * @param callable $onAuthKeyReady function(AuthKey $authKey)
     *
     * @throws TGException
     */
    public function createAuthKey(callable $onAuthKeyReady): void
    {
        $this->requestForPQ(function (ResPQ $pqResponse) use ($onAuthKeyReady) {
            $primes = $this->findPrimes($pqResponse->getPq());
            $this->requestDHParams($primes, $pqResponse, function ($dhResponse) use ($onAuthKeyReady, $pqResponse) {
                $dhParams = $this->decryptDHResponse($dhResponse, $pqResponse);
                $this->setClientDHParams(
                    $dhParams,
                    $pqResponse,
                    function (AuthParams $authKeyParams) use ($onAuthKeyReady) {
                        $onAuthKeyReady(AuthKeyCreator::createActual(
                            $authKeyParams->getAuthKey(),
                            $authKeyParams->getServerSalt(),
                            $this->dc
                        ));
                    }
                );
            });
        });
    }

    /**
     * @param callable $cb function(ResPQ $response)
     *
     * @throws TGException
     */
    private function requestForPQ(callable $cb): void
    {
        $request = new req_pq_multi($this->oldClientNonce);
        $this->socketContainer->getResponseAsync($request, function ($response) use ($cb) {
            $pqResponse = new ResPQ($response);

            if (strcmp($pqResponse->getClientNonce(), $this->oldClientNonce) !== 0) {
                throw new TGException(TGException::ERR_AUTH_INCORRECT_CLIENT_NONCE);
            }
            if (strlen($pqResponse->getServerNonce()) !== 16) {
                throw new TGException(TGException::ERR_AUTH_INCORRECT_SERVER_NONCE);
            }
            $this->obtainedServerNonce = $pqResponse->getServerNonce();
            $cb($pqResponse);
        });
    }

    /**
     * @param PQ       $pq
     * @param ResPQ    $pqData
     * @param callable $cb     function(string)
     *
     * @throws TGException
     */
    private function requestDHParams(PQ $pq, ResPQ $pqData, callable $cb): void
    {
        // prepare object
        $data = $this->getPqInnerDataMessage($pqData->getPq(), $pq->getP(), $pq->getQ(), $this->oldClientNonce, $pqData->getServerNonce(), $this->newClientNonce);
        $data = $data->toBinary();

        // obtain certificate by fingerprint
        $certificate = $this->getCertificate($pqData->getFingerprints());

        $data_with_hash = sha1($data, true).$data;
        $paddingSize = 255 - strlen($data_with_hash);
        /** @noinspection CryptographicallySecureRandomnessInspection */
        $randomBytes = openssl_random_pseudo_bytes($paddingSize, $strong);
        if ($strong === false || $randomBytes === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }
        $data_with_hash .= $randomBytes;
        $encryptedData = $this->rsa->encrypt($data_with_hash, $certificate->getPublicKey());

        // send object
        $request = new req_dh_params($this->oldClientNonce, $pqData->getServerNonce(), $pq->getP(), $pq->getQ(), $certificate->getFingerPrint(), $encryptedData);
        $this->socketContainer->getResponseAsync($request, function (AnonymousMessage $response) use ($cb) {
            $dhResponse = new DHReq($response);

            if (strcmp($dhResponse->getClientNonce(), $this->oldClientNonce) !== 0) {
                throw new TGException(TGException::ERR_AUTH_INCORRECT_CLIENT_NONCE);
            }
            if (strcmp($dhResponse->getServerNonce(), $this->obtainedServerNonce) !== 0) {
                throw new TGException(TGException::ERR_AUTH_INCORRECT_SERVER_NONCE);
            }
            $cb($dhResponse->getEncryptedAnswer());
        });
    }

    /**
     * @param int[] $receivedFingerPrints
     *
     * @throws TGException
     *
     * @return Certificate
     */
    private function getCertificate(array $receivedFingerPrints): Certificate
    {
        foreach ($receivedFingerPrints as $fingerPrint) {
            $certificate = Certificate::getCertificateByFingerPrint($fingerPrint);
            if ($certificate) {
                Logger::log('Selected fingerprint', $fingerPrint);

                return $certificate;
            }
        }

        throw new TGException(
            TGException::ERR_AUTH_CERT_FINGERPRINT_NOT_FOUND,
            'fingerprints: '.print_r($receivedFingerPrints, true)
        );
    }

    /**
     * @param int $pq
     *
     * @throws TGException
     *
     * @return PQ
     */
    private function findPrimes(int $pq): PQ
    {
        Logger::log('Factorize', $pq);

        return (new GmpFactorizer())->factorize($pq);
    }

    /**
     * @param string $encryptedAnswer
     * @param ResPQ  $pqResponse
     *
     * @throws TGException
     *
     * @return DHServerInnerData
     */
    private function decryptDHResponse(string $encryptedAnswer, ResPQ $pqResponse): DHServerInnerData
    {
        $material1 = $this->newClientNonce.$pqResponse->getServerNonce();
        $material2 = $pqResponse->getServerNonce().$this->newClientNonce;
        $this->tmpAesKey = sha1($material1, true).substr(sha1($material2, true), 0, 12);

        $material3 = $this->newClientNonce.$this->newClientNonce;
        $material4 = $this->newClientNonce;
        $this->tmpAesIV = substr(sha1($material2, true), 12, 8).sha1($material3, true).substr($material4, 0, 4);

        $answer = $this->aes->decryptIgeMode($encryptedAnswer, $this->tmpAesKey, $this->tmpAesIV);

        return $this->createDHInnerDataObject($answer);
    }

    /**
     * @param string $decryptedResponse
     *
     * @throws TGException
     *
     * @return DHServerInnerData
     */
    private function createDHInnerDataObject(string $decryptedResponse): DHServerInnerData
    {
        $messageWithoutHeaders = substr($decryptedResponse, 20, -8);
        $dhInnerData = (new OwnDeserializer())->deserialize($messageWithoutHeaders);

        return new DHServerInnerData($dhInnerData);
    }

    /**
     * @param DHServerInnerData $dhParams
     * @param ResPQ             $pqParams
     * @param callable          $cb       function(AuthParams $params)
     *
     * @throws TGException
     */
    private function setClientDHParams(DHServerInnerData $dhParams, ResPQ $pqParams, callable $cb): void
    {
        /** @noinspection CryptographicallySecureRandomnessInspection */
        $b = openssl_random_pseudo_bytes(256, $strong);
        if ($strong === false || $b === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }
        $g_b = $this->powMod->powMod($dhParams->getG(), $b, $dhParams->getDhPrime());

        $data = new client_dh_inner_data($this->oldClientNonce, $pqParams->getServerNonce(), 0, $g_b);
        $data = $data->toBinary();
        $data_with_hash = sha1($data, true).$data;
        $paddingSize = 16 - strlen($data_with_hash) % 16;
        /** @noinspection CryptographicallySecureRandomnessInspection */
        $randomBytes = openssl_random_pseudo_bytes($paddingSize, $strong);
        if ($strong === false || $randomBytes === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }
        $data_with_hash .= $randomBytes;
        $encrypted_data = $this->aes->encryptIgeMode($data_with_hash, $this->tmpAesKey, $this->tmpAesIV);

        $request = new set_client_dh_params($this->oldClientNonce, $pqParams->getServerNonce(), $encrypted_data);
        $this->socketContainer->getResponseAsync($request, function ($response) use ($cb, $dhParams, $b) {
            $dh_params_answer = new DHGenOk($response);

            if (strcmp($dh_params_answer->getClientNonce(), $this->oldClientNonce) !== 0) {
                throw new TGException(TGException::ERR_AUTH_INCORRECT_CLIENT_NONCE);
            }
            if (strcmp($dh_params_answer->getServerNonce(), $this->obtainedServerNonce) !== 0) {
                throw new TGException(TGException::ERR_AUTH_INCORRECT_SERVER_NONCE);
            }
            $initialServerSalt = substr($this->newClientNonce, 0, 8) ^ substr($this->obtainedServerNonce, 0, 8);
            $authKey = $this->powMod->powMod($dhParams->getGA(), $b, $dhParams->getDhPrime());

            if (strlen($authKey) !== 256) {
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_LENGTH, bin2hex($authKey));
            }
            if (strlen($initialServerSalt) !== 8) {
                throw new TGException(TGException::ERR_AUTH_SALT_BAD_LENGTH, bin2hex($initialServerSalt));
            }
            $cb(new AuthParams($authKey, $initialServerSalt));
        });
    }

    /**
     * @throws TGException
     */
    public function poll(): void
    {
        /** @noinspection UnusedFunctionResultInspection */
        $this->socketContainer->readMessage();
    }
}
