<?php

declare(strict_types=1);

namespace Integration\Scenario;

use Helpers\NullBasicClientGenerator;
use Helpers\TestClientGenerator;
use Helpers\TraceConverter\TraceConverterJsonToText;
use JsonException;
use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\InfoObtainingClient\Models\PictureModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Scenario\GroupPhotosScenario;
use TelegramOSINT\Scenario\Models\OptionalDateRange;

class GroupPhotosScenarioTest extends TestCase
{
    private const DEFAULT_AUTHKEY = '79803100357:7b22646576696365223a2273616d73756e6747542d4e38303030222c22616e64726f696453646b56657273696f6e223a2253444b203231222c2266697273744e616d65223a226e616d653538666630303735222c226c6173744e616d65223a226c6173746e616d656264623634623966222c226465766963654c616e67223a2272752d7275222c226170704c616e67223a227275222c2261707056657273696f6e223a22342e382e3131222c2261707056657273696f6e436f6465223a223133313831222c226c6179657256657273696f6e223a38327d:XrYgWUps5khLnDLE/5c9buuAMLQsIqjv8WyPriN0bZ1ePREdBPPfdbc0W+Fvr+KKRsg8lm+D8mvoe/tcwQ9SX1hyGqqu0Qc7HtRr9Y+OI1UL47CH/UdMDhaeMMdPIulMGrTLJJJW0bG3IFnLC+5hUkk7gH90agg4WGzNjBgz3e90aZ3nsgovefrQLT2549aklGOW3+rbXBuID3iIOLu+A1hafuwqhS3Z3TGi1AuYqZcGxDzZVOn9OlFjVBv2/c+VeqiwTDqwg5Pq79edMPBxluOrXUOEaZDBXcqznDwk1lJ7zhgX/cHHU9isrgdzO4qzt+gNZ71ybYx1JxRrx6P9/A==:7b2263726561746564223a313533323434373530342c226170695f6964223a362c2264635f6964223a322c2264635f6970223a223134392e3135342e3136372e3530222c2264635f706f7274223a3434337d';
    private const DEFAULT_TRACE_PATH = '/traces/group-photos.json';
    private const TRACE_PATH_BY_DEEPLINK = '/traces/group-photos-by-deeplink.json';
    private const TRACE_PATH_BY_USER = '/traces/group-photos-by-user.json';
    private const CHANNEL_TRACE_PATH = '/traces/channel-photos.json';
    private const CHANNEL_ID = 1229718840;
    private const DEFAULT_FILE_SIZE = 1;
    private const TIMEOUT = 0.15;
    private const DEFAULT_GROUP_DEEPLINK = 'https://t.me/asfaefegw';
    private const START_TS_20190101 = 1546300800;
    private const END_TS_20200202 = 1580601600;
    private const END_TS_20190202 = 1549065600;

    /**
     * We expect one photo to be loaded in basic scenario without limits and without group id
     *
     * @throws TGException
     * @throws JsonException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_basic_get_scenario(): void {
        $count = 0;
        $saveHandler = function (PictureModel $model, /** @noinspection PhpUnusedParameterInspection */ int $id) use (&$count) {
            $count++;
            $this->assertEquals(self::DEFAULT_FILE_SIZE, strlen($model->bytes));
            $this->assertEquals(1578494852, $model->modificationTime);
        };
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::DEFAULT_TRACE_PATH);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true, 512, JSON_THROW_ON_ERROR));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosScenario(new OptionalDateRange(), null, $saveHandler, $testGenerator);
        $client->setTimeout(self::TIMEOUT);
        $client->startActions();
        $this->assertEquals(1, $count);
    }

    /**
     * We expect one photo to be loaded in basic scenario without limits by group id
     *
     * @throws TGException|JsonException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_channel_get_scenario(): void {
        $count = 0;
        $saveHandler = function (PictureModel $model, /** @noinspection PhpUnusedParameterInspection */ int $id) use (&$count) {
            $count++;
            $this->assertEquals(self::DEFAULT_FILE_SIZE, strlen($model->bytes));
            $this->assertEquals(1578467676, $model->modificationTime);
        };
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::CHANNEL_TRACE_PATH);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true, 512, JSON_THROW_ON_ERROR));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosScenario(new OptionalDateRange(), null, $saveHandler, $testGenerator);
        $client->setGroupId(self::CHANNEL_ID);
        $client->setTimeout(self::TIMEOUT);
        $client->startActions();
        $this->assertEquals(1, $count);
    }

    /**
     * We expect one photo to be loaded in basic scenario with limits by group id
     *
     * @throws TGException|JsonException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_channel_get_with_time_limit_scenario(): void {
        $count = 0;
        $saveHandler = function (PictureModel $model, /** @noinspection PhpUnusedParameterInspection */ int $id) use (&$count) {
            $count++;
            $this->assertEquals(self::DEFAULT_FILE_SIZE, strlen($model->bytes));
            $this->assertEquals(1578467676, $model->modificationTime);
        };
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::CHANNEL_TRACE_PATH);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true, 512, JSON_THROW_ON_ERROR));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosScenario(
            new OptionalDateRange(
                self::START_TS_20190101, // 20190101
                self::END_TS_20200202 // 20200202
            ),
            null,
            $saveHandler,
            $testGenerator
        );
        $client->setGroupId(self::CHANNEL_ID);
        $client->setTimeout(self::TIMEOUT);
        $client->startActions();
        $this->assertEquals(1, $count);
    }

    /**
     * We expect one photo to be loaded in basic scenario with limits by group id
     *
     * @throws TGException|JsonException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_channel_get_with_time_limit_scenario_empty(): void {
        $count = 0;
        $saveHandler = function (PictureModel $model, /** @noinspection PhpUnusedParameterInspection */ int $id) use (&$count) {
            $count++;
            $this->assertEquals(self::DEFAULT_FILE_SIZE, strlen($model->bytes));
            $this->assertEquals(1578467676, $model->modificationTime);
        };
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::CHANNEL_TRACE_PATH);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true, 512, JSON_THROW_ON_ERROR));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosScenario(
            new OptionalDateRange(
                self::START_TS_20190101, // 20190101
                self::END_TS_20190202 // 20190202
            ),
            null,
            $saveHandler,
            $testGenerator
        );
        $client->setGroupId(self::CHANNEL_ID);
        $client->setTimeout(self::TIMEOUT);
        $client->startActions();
        $this->assertEquals(0, $count);
    }

    /**
     * We expect two photos to be loaded in basic scenario with chat url
     *
     * @throws TGException|JsonException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_basic_get_with_group_scenario_without_limits(): void {
        $count = 0;
        $saveHandler = static function (
            /** @noinspection PhpUnusedParameterInspection */
            PictureModel $model,
            /** @noinspection PhpUnusedParameterInspection */
            int $id
        ) use (&$count) {
            $count++;
        };
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::TRACE_PATH_BY_DEEPLINK);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true, 512, JSON_THROW_ON_ERROR));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosScenario(
            new OptionalDateRange(),
            null,
            $saveHandler,
            $testGenerator
        );
        $client->setDeepLink(self::DEFAULT_GROUP_DEEPLINK);
        $client->setTimeout(self::TIMEOUT);
        $client->startActions();
        $this->assertEquals(2, $count);
    }

    /**
     * We expect two photos to be loaded in basic scenario with chat url
     *
     * @throws TGException|JsonException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_basic_get_with_group_scenario(): void {
        $count = 0;
        $saveHandler = static function (
            /** @noinspection PhpUnusedParameterInspection */
            PictureModel $model,
            /** @noinspection PhpUnusedParameterInspection */
            int $id
        ) use (&$count) {
            $count++;
        };
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::TRACE_PATH_BY_DEEPLINK);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true, 512, JSON_THROW_ON_ERROR));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosScenario(
            new OptionalDateRange(),
            null,
            $saveHandler,
            $testGenerator
        );
        $client->setDeepLink(self::DEFAULT_GROUP_DEEPLINK);
        $client->setTimeout(self::TIMEOUT);
        $client->startActions();
        $this->assertEquals(2, $count);
    }

    /**
     * We expect two photos to be loaded in basic scenario with chat url and user filter
     *
     * @throws TGException|JsonException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_basic_get_with_user_scenario(): void {
        $count = 0;
        $saveHandler = static function (
            /** @noinspection PhpUnusedParameterInspection */
            PictureModel $model,
            /** @noinspection PhpUnusedParameterInspection */
            int $id
        ) use (&$count) {
            $count++;
        };
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::TRACE_PATH_BY_USER);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true, 512, JSON_THROW_ON_ERROR));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        /** @noinspection SpellCheckingInspection */
        $client = new GroupPhotosScenario(
            new OptionalDateRange(),
            'ntlvikhhjofnekge',
            $saveHandler,
            $testGenerator
        );
        $client->setDeepLink(self::DEFAULT_GROUP_DEEPLINK);
        $client->setTimeout(self::TIMEOUT);
        $client->startActions();
        $this->assertEquals(2, $count);
    }

    /**
     * We expect no photos to be loaded in basic scenario with date limits
     *
     * @throws TGException|JsonException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_basic_scenario_with_date_limit_none(): void {
        $count = 0;
        $saveHandler = function (PictureModel $model, /** @noinspection PhpUnusedParameterInspection */ int $id) use (&$count) {
            $count++;
            $this->assertEquals(self::DEFAULT_FILE_SIZE, strlen($model->bytes));
            $this->assertEquals(1578494852, $model->modificationTime);
        };
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::DEFAULT_TRACE_PATH);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true, 512, JSON_THROW_ON_ERROR));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosScenario(
            new OptionalDateRange(
                self::START_TS_20190101, // 20190101
                self::END_TS_20190202 // 20190202
            ),
            null,
            $saveHandler,
            $testGenerator
        );
        $client->setTimeout(self::TIMEOUT);
        $client->startActions();
        $this->assertEquals(0, $count);
    }

    /**
     * We expect one photo to be loaded in basic scenario with date limits
     *
     * @throws TGException|JsonException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_basic_scenario_with_date_limit_one(): void {
        $count = 0;
        $saveHandler = static function (
            /** @noinspection PhpUnusedParameterInspection */
            PictureModel $model,
            /** @noinspection PhpUnusedParameterInspection */
            int $id
        ) use (&$count) {
            $count++;
        };
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::DEFAULT_TRACE_PATH);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true, 512, JSON_THROW_ON_ERROR));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosScenario(
            new OptionalDateRange(
                self::START_TS_20190101, // 20190101
                self::END_TS_20200202 // 20200202
            ),
            null,
            $saveHandler,
            $testGenerator
        );
        $client->setTimeout(self::TIMEOUT);
        $client->startActions();
        $this->assertEquals(1, $count);
    }
}
