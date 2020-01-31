<?php

declare(strict_types=1);

namespace TelegramOSINT;

class LibConfig
{
    /* ================================================================ Debug output */

    const LOGGER_ENABLED = true;
    const LOG_LEVEL = 'debug'; // supported: info, debug

    /* ================================================================ Socks proxy params  */

    // connection to proxy timeout
    const CONN_SOCKET_PROXY_TIMEOUT_SEC = 3;

    /* ================================================================ Telegram socket params */

    // persistent message read timeout from socket
    const CONN_SOCKET_TIMEOUT_PERSISTENT_READ_MS = 1500;
    // response on RPC request max timeout
    const CONN_SOCKET_TIMEOUT_WAIT_RESPONSE_MS = 1500;
    // sleep in microseconds between 2 reads from socket during response waiting
    const CONN_SOCKET_RESPONSE_DELAY_MICROS = 10000;
    // ping server interval
    const CONN_PING_INTERVAL_SEC = 25;

    /* ================================================================ Default DataCenter config (Europe and CIS) */

    const DC_DEFAULT_IP = '149.154.167.50';
    const DC_DEFAULT_PORT = 443;
    const DC_DEFAULT_ID = 2;

    /* ================================================================  Default client info */

    const APP_DEFAULT_DEVICE_LANG_CODE = 'en-us';
    const APP_DEFAULT_LANG_CODE = 'en';
    const APP_DEFAULT_VERSION = '5.14.0';
    const APP_DEFAULT_VERSION_CODE = '18517'; // looks like 5th digit is always "7"
    const APP_DEFAULT_LANG_PACK = 'android';
    const APP_DEFAULT_TL_LAYER_VERSION = 109;

    const ENV_AUTHKEY = 'BOT'; // env variable for authkey path
}
