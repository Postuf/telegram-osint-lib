<?php

declare(strict_types=1);

namespace TelegramOSINT\TGConnection\SocketMessenger;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\TGConnection\Socket\Socket;

abstract class TgSocketMessenger extends BaseSocketMessenger
{
    protected const HEADER_LENGTH_BYTES = 4;
    protected Socket $socket;
    /** @var ReadState|null */
    private ?ReadState $readState = null;

    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @throws TGException
     *
     * @return string|null
     */
    protected function readPacket(): ?string
    {
        if (!$this->readState) {
            $this->readState = new ReadState();
        }
        if (!$this->readState->getLength()) {
            // header
            $lengthValue = $this->socket->readBinary(self::HEADER_LENGTH_BYTES);
            $readLength = strlen((string) $lengthValue);
            /** @noinspection TypeUnsafeComparisonInspection */
            if ($readLength == 0) {
                return null;
            }
            if ($readLength !== self::HEADER_LENGTH_BYTES) {
                throw new TGException(TGException::ERR_DESERIALIZER_BROKEN_BINARY_READ, self::HEADER_LENGTH_BYTES.'!='.$readLength);
            }
            // data
            $payloadLength = unpack('I', $lengthValue)[1] - self::HEADER_LENGTH_BYTES;
            $this->readState->setLengthValue($lengthValue);
            $this->readState->setLength($payloadLength);
        } else {
            $payloadLength = $this->readState->getLength();
            $lengthValue = $this->readState->getLengthValue();
        }
        $lengthToRead = $payloadLength - $this->readState->getCurrentLength();
        $newPayload = $this->socket->readBinary($lengthToRead);
        if ((string) $newPayload !== '') {
            $this->readState->addRead($newPayload);
        }
        if (!$this->readState->ready()) {
            $timeDiff = 1000.0 * (microtime(true) - $this->readState->getTimeStart());
            if ($timeDiff > LibConfig::CONN_SOCKET_TIMEOUT_PERSISTENT_READ_MS) {
                $timeDiffFormatted = number_format($timeDiff, 2);

                throw new TGException(
                    TGException::ERR_CONNECTION_SOCKET_READ_TIMEOUT,
                    "timeout of $timeDiffFormatted ms > ".LibConfig::CONN_SOCKET_TIMEOUT_PERSISTENT_READ_MS
                );
            }

            return null;
        }

        // full TL packet
        $packet = $lengthValue.$this->readState->getPayload();
        $this->readState = null;

        return $packet;
    }
}
