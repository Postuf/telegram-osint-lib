<?php

declare(strict_types=1);

namespace Unit\Registration;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Registration\AccountInfo;
use TelegramOSINT\Registration\DeviceGenerator\DeviceResource;

class AccountInfoTest extends TestCase
{
    /**
     * @throws TGException
     * @noinspection SpellCheckingInspection
     */
    public function test_serialize(): void
    {
        $accountInfo = AccountInfo::deserializeFromJson(
            <<<'TAG'
        {
        "device":"x1",
        "androidSdkVersion":"x2",
        "firstName":"x3",
        "lastName":"x4",
        "deviceLang":"x5",
        "appLang":"x6",
        "appVersion":"x7",
        "appVersionCode":"x8",
        "layerVersion":105
        }
TAG
        );
        $unserializedAccountInfo = AccountInfo::deserializeFromJson($accountInfo->serializeToJson());
        self::assertEquals('x1', $unserializedAccountInfo->getDevice());
        self::assertEquals('SDK '.DeviceResource::getMinSdkVersion(), $unserializedAccountInfo->getAndroidSdkVersion());
        self::assertEquals('x3', $unserializedAccountInfo->getFirstName());
        self::assertEquals('x4', $unserializedAccountInfo->getLastName());
        self::assertEquals('x5', $unserializedAccountInfo->getDeviceLang());
        self::assertEquals('x6', $unserializedAccountInfo->getAppLang());
        self::assertEquals('x7', $unserializedAccountInfo->getAppVersion());
        self::assertEquals('x8', $unserializedAccountInfo->getAppVersionCode());
        self::assertEquals(105, $unserializedAccountInfo->getLayerVersion());
    }
}
