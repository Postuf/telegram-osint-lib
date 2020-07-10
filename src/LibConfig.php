<?php

/** @noinspection PhpUnused
 * @noinspection UnknownInspectionInspection
 */
declare(strict_types=1);

namespace TelegramOSINT;

class LibConfig
{
    /* ================================================================ Debug output */

    public const LOGGER_ENABLED = true;
    public const LOG_LEVEL = 'debug'; // supported: info, debug

    /* ================================================================ Socks proxy params  */

    // connection to proxy timeout
    public const CONN_SOCKET_PROXY_TIMEOUT_SEC = 5;

    /* ================================================================ Telegram socket params */

    // persistent message read timeout from socket
    public const CONN_SOCKET_TIMEOUT_PERSISTENT_READ_MS = 1500;
    // response on RPC request max timeout
    public const CONN_SOCKET_TIMEOUT_WAIT_RESPONSE_MS = 1500;
    // sleep in microseconds between 2 reads from socket during response waiting
    public const CONN_SOCKET_RESPONSE_DELAY_MICROS = 10000;
    // ping server interval
    public const CONN_PING_INTERVAL_SEC = 25;

    /* ================================================================ Default DataCenter config (Europe and CIS) */

    public const DC_DEFAULT_IP = '149.154.167.50';
    public const DC_DEFAULT_PORT = 443;
    public const DC_DEFAULT_ID = 2;

    /* ================================================================ Api keys */

    // Official App
    public const APP_API_HASH = '014b35b6184100b085b0d0572f9b5103';
    public const APP_API_ID = 4;
    public const APP_CERT_SHA256 = '49C1522548EBACD46CE322B6FD47F6092BB745D0F88082145CAF35E14DCC38E1';
    public const APP_TZ_START = 0;

    /* ================================================================  Default client info */

    public const APP_DEFAULT_DEVICE_LANG_CODE = 'en-us';
    public const APP_DEFAULT_LANG_CODE = 'en';
    public const APP_DEFAULT_VERSION = '6.2.0';
    // see https://www.apkmirror.com/apk/telegram-fz-llc/telegram/telegram-6-0-1-release/
    // arm64-v8a for android 6+ has 5th digit always "7"
    public const APP_DEFAULT_VERSION_CODE = '19867';
    public const APP_DEFAULT_LANG_PACK = 'android';
    public const APP_DEFAULT_TL_LAYER_VERSION = 114;

    public const ENV_AUTHKEY = 'BOT'; // env variable for authkey path
}
