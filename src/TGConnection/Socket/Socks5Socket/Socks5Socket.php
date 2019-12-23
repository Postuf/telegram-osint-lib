<?php

/**
 * Manage native socket as socks5-connected socket.
 * Works only with SOCKS v5, supports only basic
 * authorization - without login:password
 */
class Socks5Socket
{
    /**
     * Native socket
     * @var resource
     */
    private $socksSocket = null;
    /**
     * Domain name, not IP address
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;
    /**
     * @var SocksProxy
     */
    private $proxy;
    /**
     * @var int
     */
    private $timeoutSeconds;


    /**
     * @param SocksProxy $proxy
     * @param int $timeOutSeconds
     */
    public function __construct(SocksProxy $proxy, int $timeOutSeconds)
    {
        $this->proxy = $proxy;
        $this->timeoutSeconds = $timeOutSeconds;
    }


    /**
     * @param string $host containing domain name
     * @param int $port
     * @return resource
     * @throws SocksException
     */
    public function createConnected($host, $port)
    {
        $this->host = $host;
        $this->port = $port;

        $this->socksSocket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->configureSocket();

        if ($this->socksSocket !== false) {
            $result = @socket_connect($this->socksSocket, $this->proxy->getServer(), $this->proxy->getPort());
            if ($result === false)
                throw new SocksException(SocksException::UNREACHABLE_PROXY, 'on connect');

            $this->socksGreeting();
            $this->socksConnect();
        }

        return $this->socksSocket;
    }


    /**
     * Sets connection timeout on socket
     */
    private function configureSocket()
    {
        $timeoutConfig = [
            "sec" => $this->timeoutSeconds,
            "usec" => 0
        ];
        socket_set_option($this->socksSocket, SOL_SOCKET, SO_RCVTIMEO, $timeoutConfig);
        socket_set_option($this->socksSocket, SOL_SOCKET, SO_SNDTIMEO, $timeoutConfig);
    }


    /**
     * SOCKS protocol: https://ru.wikipedia.org/wiki/SOCKS
     * Client`s first request and server`s first response
     *
     * @throws SocksException
     */
    private function socksGreeting()
    {
        // client hello
        $helloMsg = "\x05\x02\x00\x02";
        $this->write($helloMsg);

        // server hello
        $socksConfig = $this->read(2);
        if(!$socksConfig)
            throw new SocksException(SocksException::UNREACHABLE_PROXY, 'on greeting');

        $this->checkServerGreetedClient($socksConfig);
    }


    /**
     * @param string $serverGreeting binary from socket
     * @throws SocksException
     */
    private function checkServerGreetedClient($serverGreeting)
    {
        $socksVersion = ord($serverGreeting[0]);
        $socksAuthType = ord($serverGreeting[1]);

        if($socksVersion != 0x05)
            throw new SocksException(SocksException::UNEXPECTED_PROTOCOL_VERSION, $socksVersion);
        if($socksAuthType != 0x00 && $socksAuthType != 0x02)
            throw new SocksException(SocksException::UNSUPPORTED_AUTH_TYPE, $socksAuthType);

        if($socksAuthType == 0x02)
            $this->authorizeOnProxy();
    }


    /**
     * @throws SocksException
     */
    private function authorizeOnProxy()
    {
        $userName = $this->proxy->getLogin();
        $ulength = chr(strlen($userName));

        $password = $this->proxy->getPassword();
        $plength = chr(strlen($password));

        $this->write("\x01".$ulength.$userName.$plength.$password);
        $connectionStatus = $this->read(2);

        if($connectionStatus[0] != "\x01" || $connectionStatus[1] != "\x00")
            throw new SocksException(SocksException::AUTH_FAILED);
    }


    /**
     * SOCKS protocol: https://ru.wikipedia.org/wiki/SOCKS
     * Client`s second request and server`s second response
     * @throws SocksException
     */
    private function socksConnect()
    {
        $host = $this->host;
        $port = $this->port;
        $hostnameLenBinary = chr(strlen($host));
        $portBinary = unpack("C*", pack("L", $port));
        $portBinary = chr($portBinary[2]).chr($portBinary[1]);

        // client connection request
        $establishmentMsg = "\x05\x01\x00\x03".$hostnameLenBinary.$host.$portBinary;
        $this->write($establishmentMsg);

        // server connection response
        $connectionStatus = $this->read(1024);
        if(!$connectionStatus)
            throw new SocksException(SocksException::RESPONSE_WAS_NOT_RECEIVED);

        $this->checkConnectionEstablished($connectionStatus);
    }


    /**
     * @param string $serverConnectionResponse
     * @throws SocksException
     */
    private function checkConnectionEstablished($serverConnectionResponse)
    {
        $socksVersion = ord($serverConnectionResponse[0]);
        $responseCode = ord($serverConnectionResponse[1]);

        if($socksVersion != 0x05)
            throw new SocksException(SocksException::UNEXPECTED_PROTOCOL_VERSION, $socksVersion);
        if($responseCode != 0x00)
            throw new SocksException(SocksException::CONNECTION_NOT_ESTABLISHED, $responseCode);
    }


    /**
     * @param string $data binary to write
     * @return int bytes actually written
     */
    public function write($data)
    {
        return @socket_write($this->socksSocket, $data);
    }

    /**
     * @param int $bytesCount bytes count to read
     * @return string binary
     */
    public function read($bytesCount)
    {
        return @socket_read($this->socksSocket, $bytesCount);
    }
}