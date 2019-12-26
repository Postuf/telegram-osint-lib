<?php


namespace TGConnection\Socket;


use SocksProxyAsync\SocketAsync;
use SocksProxyAsync\SocksException;

class SocketAsyncTg extends SocketAsync
{
    /**
     * @return resource
     */
    public function getSocksSocket() {
        return $this->socksSocket;
    }

    /**
     * @throws SocksException
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function poll(): void
    {
        switch ($this->step->getStep()) {
            case 0:
                $this->createSocket();
                $this->step->setStep(1);
                break;
            case 1:
                if($this->connectSocket()){
                    $this->writeSocksGreeting();
                    $this->step->setStep(2);
                }
                break;
            case 2:
                $socksGreetingConfig = $this->readSocksGreeting();
                if ($socksGreetingConfig){
                    $this->checkServerGreetedClient($socksGreetingConfig);
                    if($this->checkGreetngWithAuth($socksGreetingConfig)){
                        $this->writeSocksAuth();
                        $this->step->setStep(3);
                    } else {
                        $this->step->setStep(4);
                    }
                }
                break;
            case 3:
                if ($this->readSocksAuthStatus())
                    $this->step->setStep(4);
                break;
            case 4:
                $this->connectSocksSocket();
                $this->step->setStep(5);
                break;
            case 5:
                if(!$this->isReady && $this->readSocksConnectStatus()) {
                    $this->isReady = true;
                    $this->step->setStep(6);
                    return;
                }
                break;
            case 6:
                return;
        }

        try{
            $this->step->checkIfStepStuck();
        } catch (SocksException $e){
            $this->stop();
            throw $e;
        }
    }

}