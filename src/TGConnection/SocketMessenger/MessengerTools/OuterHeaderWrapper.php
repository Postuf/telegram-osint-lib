<?php

namespace TGConnection\SocketMessenger\MessengerTools;

use Exception\TGException;

class OuterHeaderWrapper
{
    /**
     * @var int
     */
    private $out_seq_no = 0;
    /**
     * @var int
     */
    private $in_seq_no = 0;

    /**
     * @param string $binaryPayload
     *
     * @return string
     */
    public function wrap($binaryPayload)
    {
        $wrapped = $this->wrapPayloadWithSeqCounterAndCRC($binaryPayload);
        $this->out_seq_no++;

        return $wrapped;
    }

    /**
     * @param string $binaryPayload
     *
     * @throws TGException
     *
     * @return false|string
     */
    public function unwrap($binaryPayload)
    {
        $length_value = substr($binaryPayload, 0, 4);
        $length = unpack('I', $length_value)[1];

        $in_seq_no_value = substr($binaryPayload, 4, 4);
        $in_seq_no = unpack('I', $in_seq_no_value)[1];

        $payload = substr($binaryPayload, 8, -4);
        $foreignCrc32 = substr($binaryPayload, -4);
        $mySrc32 = strrev(hash('crc32b', $length_value.$in_seq_no_value.$payload, true));

        $fullPacketSize = strlen($length_value) + strlen($in_seq_no_value) + strlen($payload) + strlen($foreignCrc32);

        if($length != $fullPacketSize)
            throw new TGException(TGException::ERR_TL_CONTAINER_BAD_SIZE);
        if($in_seq_no != $this->in_seq_no++)
            throw new TGException(TGException::ERR_TL_CONTAINER_BAD_SEQNO);
        if(strcmp($mySrc32, $foreignCrc32) != 0)
            throw new TGException(TGException::ERR_TL_CONTAINER_BAD_CRC32);

        return $payload;
    }

    private function wrapPayloadWithSeqCounterAndCRC($payload)
    {
        $length = strlen($payload) + 12; /* размер пакета(+12B:
                    4 - размер,
                     4 - порядковый номер запроса,
                     4 - контрольная сумма) */

        //размер пакета и порядковый номер запроса добавляется в начало
        $payload = pack('II', $length, $this->out_seq_no).$payload;

        //контролная сумма добавляется в конец
        $crc32 = hexdec(hash('crc32b', $payload));
        $payload .= pack('I', $crc32);

        return $payload;
    }
}
