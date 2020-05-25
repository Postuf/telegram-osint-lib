<?php

/** @noinspection PhpUnused */
declare(strict_types=1);

namespace TelegramOSINT\Exception;

use Exception;
use ReflectionClass;

class TGException extends Exception
{

    // authorization errors
    public const ERR_AUTH_CLI_FACTORIZATION_BAD_OUTPUT                                = 100000;
    public const ERR_AUTH_CLI_FACTOR_BAD_PARSING                                      = 101000;
    public const ERR_AUTH_GMP_FACTOR_WRONG_RESULT                                     = 102000;
    public const ERR_AUTH_INCORRECT_CLIENT_NONCE                                      = 103000;
    public const ERR_AUTH_INCORRECT_SERVER_NONCE                                      = 104000;
    public const ERR_AUTH_KEY_BAD_FORMAT                                              = 105000;
    public const ERR_AUTH_KEY_BAD_LENGTH                                              = 106000;
    public const ERR_AUTH_SALT_BAD_LENGTH                                             = 107000;
    public const ERR_AUTH_CERT_FINGERPRINT_NOT_FOUND                                  = 108000;
    public const ERR_AUTH_KEY_NOT_SUPPORTED                                           = 113000;
    public const ERR_AUTH_KEY_BAD_ACCOUNT_INFO                                        = 114000;
    public const ERR_AUTH_EXPORT_FAILED                                               = 115000;

    // telegram connection problems
    public const ERR_CONNECTION_SHUTDOWN                                              = 200000;
    public const ERR_CANT_CONNECT                                                     = 201000;
    public const ERR_CONNECTION_SOCKET_CLOSED                                         = 202000;
    public const ERR_CONNECTION_EMPTY_BUFFER_WRITE                                    = 203000;
    public const ERR_CONNECTION_SOCKET_READ_TIMEOUT                                   = 204000;
    public const ERR_CONNECTION_SOCKET_TERMINATED                                     = 205000;
    public const ERR_CONNECTION_BAD_PING_COMBINATION                                  = 206000;

    // errors in client implementations
    public const ERR_CLIENT_CONTACTS_NOT_CLEANED                                      = 300000;
    public const ERR_CLIENT_ALREADY_LOGGED_IN                                         = 303000;
    public const ERR_CLIENT_NOT_LOGGED_IN                                             = 304000;
    public const ERR_CLIENT_COULD_NOT_DELETE                                          = 306000;
    public const ERR_CLIENT_USER_PIC_UNKNOWN_FORMAT                                   = 307000;
    public const ERR_CLIENT_PICTURE_ON_UNREACHABLE_DC                                 = 308000;
    public const ERR_CLIENT_BAD_NUMBER_FORMAT                                         = 309000;
    public const ERR_CLIENT_FLOODING_ACTIONS                                          = 310000;
    public const ERR_CLIENT_ADD_PHONE_ALREADY_IN_ADDRESS_BOOK                         = 311000;
    public const ERR_CLIENT_ADD_USERNAME_ALREADY_IN_ADDRESS_BOOK                      = 312000;

    // tl message deserialization problems
    public const ERR_DESERIALIZER_NOT_TOTAL_READ                                      = 400000;
    public const ERR_DESERIALIZER_UNKNOWN_OBJECT                                      = 401000;
    public const ERR_DESERIALIZER_FIELD_BIT_MASK_NOT_PROVIDED                         = 402000;
    public const ERR_DESERIALIZER_BROKEN_BINARY_READ                                  = 403000;
    public const ERR_DESERIALIZER_VECTOR_EXPECTED                                     = 404000;

    // deserialized message to object convertion problems
    public const ERR_TL_MESSAGE_FIELD_NOT_EXISTS                                      = 500000;
    public const ERR_TL_MESSAGE_FIELD_BAD_NODE                                        = 501000;
    public const ERR_TL_MESSAGE_UNEXPECTED_OBJECT                                     = 502000;
    public const ERR_TL_MESSAGE_FIELD_BAD_SCALARS                                     = 503000;

    // registration errors
    public const ERR_REG_REQUEST_SMS_CODE_FIRST                                       = 600000;
    public const ERR_REG_COULD_NOT_SEND_CODE                                          = 601000;
    public const ERR_REG_FAILED                                                       = 602000;
    public const ERR_REG_NOT_OFFICIAL_USER                                            = 603000;
    public const ERR_REG_USER_ALREADY_EXISTS                                          = 604000;

    // MTProto container problems
    public const ERR_TL_CONTAINER_BAD_SIZE                                            = 700000;
    public const ERR_TL_CONTAINER_BAD_SEQNO                                           = 701000;
    public const ERR_TL_CONTAINER_BAD_CRC32                                           = 702000;
    public const ERR_TL_CONTAINER_BAD_AUTHKEY_ID                                      = 703000;
    public const ERR_TL_CONTAINER_BAD_AUTHKEY_ID_MUST_BE_0                            = 704000;
    public const ERR_TL_CONTAINER_BAD_MSG_KEY                                         = 705000;
    public const ERR_TL_CONTAINER_BAD_SESSION_ID                                      = 706000;
    public const ERR_TL_ENCRYPTION_ERROR                                              = 707000;

    // tl message from telegram server reporting about errors
    public const ERR_MSG_RESPONSE_TIMEOUT                                             = 800000;
    public const ERR_MSG_NETWORK_MIGRATE                                              = 801000;
    public const ERR_MSG_PHONE_MIGRATE                                                = 802000;
    public const ERR_MSG_FLOOD                                                        = 803000;
    public const ERR_MSG_USER_BANNED                                                  = 804000;
    public const ERR_MSG_RESEND_IMPOSSIBLE                                            = 805000;
    public const ERR_MSG_PHONE_BANNED                                                 = 806000;
    public const ERR_MSG_BANNED_AUTHKEY_DUPLICATED                                    = 807000;
    public const ERR_MSG_BANNED_SESSION_STOLEN                                        = 808000;
    public const ERR_MSG_IMPORT_CONTACTS_LIMIT_EXCEEDED                               = 809000;

    // asserts
    public const ERR_ASSERT_EXTENSION_MISSING                                         = 900000;
    public const ERR_ASSERT_UPDATE_USER_UNIDENTIFIED                                  = 901000;
    public const ERR_ASSERT_UPDATE_EXPIRES_TIME_LONG                                  = 902000;
    public const ERR_ASSERT_LISTENER_ALREADY_SET                                      = 905000;
    public const ERR_ASSERT_UNKNOWN_HIDDEN_STATUS                                     = 906000;

    // proxy errors
    public const ERR_PROXY_WRONG_PROXY_TYPE                                           = 1000000;
    public const ERR_PROXY_UNREACHABLE                                                = 1001000;
    public const ERR_PROXY_WRONG_SOCKS_VERSION                                        = 1002000;
    public const ERR_PROXY_UNKNOWN_AUTH_CODE                                          = 1003000;
    public const ERR_PROXY_CONNECTION_NOT_ESTABLISHED                                 = 1004000;
    public const ERR_PROXY_BAD_FORMAT                                                 = 1005000;
    public const ERR_PROXY_AUTH_FAILED                                                = 1006000;
    public const ERR_PROXY_LONG_STEP                                                  = 1007000;

    /**
     * TGException constructor.
     * @param int $code
     * @param string $clarification
     */
    public function __construct(int $code = 0, $clarification = "")
    {
        if(is_object($clarification) || is_array($clarification))
            $clarification = print_r($clarification, true);

        $clarification = $this->getMessageByCode($code) . ': ' . $clarification;
        parent::__construct($clarification, $code);
    }


    /**
     * @param int $code
     * @return string
     */
    private function getMessageByCode(int $code): string
    {
        return $this->returnConstantNameOfCode($code);
    }


    /**
     * @param int $code
     * @return string
     */
    private function returnConstantNameOfCode(int $code): string
    {
        foreach($this->getConstants() as $constantCode => $constantName) {
            if ($code === $constantCode) {
                return $constantName;
            }
        }

        return 'UNKNOWN_EXCEPTION';
    }


    /**
     * @return array
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getConstants(): array
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $reflector = new ReflectionClass(get_class($this));
        $constants = $reflector->getConstants();
        $constantArray = [];

        foreach($constants as $constant => $code) {
            $constantArray[$code] = $constant;
        }

        return $constantArray;
    }

}
