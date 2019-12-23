<?php

namespace TGConnection\SocketMessenger\MessengerTools;


class MessageIdGenerator
{

    /**
     * @var int
     */
    private $msgId = 0;


    /**
     * @return int
     */
    public function generateNext()
    {
        [$msec,$sec] = explode(" ",microtime());

        $msec *= pow(10,6);// микросекунды переводим в целое число
        $msec = $msec<<2;// умножаем на 4 (чтобы был кратен 4)

        $msgId = ($sec<<32)|$msec; // накладываем микросекунды на время * 2^32

        // сравниваем созданный идентификатор сообщения с уже использованным
        if ($msgId <= $this->msgId) { // если созданый id меньше
            $msgId = $this->msgId + 4; // то перезаписываем его
        }

        return $msgId;
    }

}