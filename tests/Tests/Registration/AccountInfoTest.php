<?php

declare(strict_types=1);

namespace Tests\Registration;

use Exception\TGException;
use PHPUnit\Framework\TestCase;
use Registration\AccountInfo;

class AccountInfoTest extends TestCase
{
    /**
     * @throws TGException
     */
    public function test_serialize(): void
    {
        $accountInfo = AccountInfo::deserializeFromJson(<<<'TAG'
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
        $this->assertEquals('x1', $unserializedAccountInfo->getDevice());
        $this->assertEquals('x2', $unserializedAccountInfo->getAndroidSdkVersion());
        $this->assertEquals('x3', $unserializedAccountInfo->getFirstName());
        $this->assertEquals('x4', $unserializedAccountInfo->getLastName());
        $this->assertEquals('x5', $unserializedAccountInfo->getDeviceLang());
        $this->assertEquals('x6', $unserializedAccountInfo->getAppLang());
        $this->assertEquals('x7', $unserializedAccountInfo->getAppVersion());
        $this->assertEquals('x8', $unserializedAccountInfo->getAppVersionCode());
        $this->assertEquals(105, $unserializedAccountInfo->getLayerVersion());
    }
}
