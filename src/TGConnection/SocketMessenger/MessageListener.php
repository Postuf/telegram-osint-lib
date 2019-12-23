<?php
namespace TGConnection\SocketMessenger;


use MTSerialization\AnonymousMessage;


interface MessageListener
{

    /**
     * @param AnonymousMessage $message
     */
    public function onMessage(AnonymousMessage $message);

}