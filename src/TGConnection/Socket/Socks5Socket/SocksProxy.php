<?php


interface SocksProxy
{

    public function getServer();
    public function getPort();
    public function getLogin();
    public function getPassword();

}