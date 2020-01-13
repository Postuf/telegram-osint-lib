<?php

declare(strict_types=1);

namespace Integration\Scenario;

use Client\InfoObtainingClient\Models\PictureModel;
use Exception\TGException;
use Helpers\NullBasicClientGenerator;
use Helpers\TestClientGenerator;
use PHPUnit\Framework\TestCase;
use Scenario\GroupPhotosClient;
use Helpers\DateParser;

class GroupPhotosClientTest extends TestCase
{
    private const DEFAULT_AUTHKEY = '79803100357:7b22646576696365223a2273616d73756e6747542d4e38303030222c22616e64726f696453646b56657273696f6e223a2253444b203231222c2266697273744e616d65223a226e616d653538666630303735222c226c6173744e616d65223a226c6173746e616d656264623634623966222c226465766963654c616e67223a2272752d7275222c226170704c616e67223a227275222c2261707056657273696f6e223a22342e382e3131222c2261707056657273696f6e436f6465223a223133313831222c226c6179657256657273696f6e223a38327d:XrYgWUps5khLnDLE/5c9buuAMLQsIqjv8WyPriN0bZ1ePREdBPPfdbc0W+Fvr+KKRsg8lm+D8mvoe/tcwQ9SX1hyGqqu0Qc7HtRr9Y+OI1UL47CH/UdMDhaeMMdPIulMGrTLJJJW0bG3IFnLC+5hUkk7gH90agg4WGzNjBgz3e90aZ3nsgovefrQLT2549aklGOW3+rbXBuID3iIOLu+A1hafuwqhS3Z3TGi1AuYqZcGxDzZVOn9OlFjVBv2/c+VeqiwTDqwg5Pq79edMPBxluOrXUOEaZDBXcqznDwk1lJ7zhgX/cHHU9isrgdzO4qzt+gNZ71ybYx1JxRrx6P9/A==:7b2263726561746564223a313533323434373530342c226170695f6964223a362c2264635f6964223a322c2264635f6970223a223134392e3135342e3136372e3530222c2264635f706f7274223a3434337d';
    private const DEFAULT_TRACE_PATH = '/traces/group-photos.txt';

    /**
     * We expect one photo to be loaded in basic scenario without limits
     *
     * @throws TGException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_basic_get_scenario(): void {
        $count = 0;
        $saveHandler = function (PictureModel $model, int $id) use (&$count) {
            $count++;
            $this->assertEquals(140179, strlen($model->bytes));
            $this->assertEquals(1578494852, $model->modificationTime);
        };
        $file = file_get_contents(__DIR__.self::DEFAULT_TRACE_PATH);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosClient(null, null, $saveHandler, $testGenerator);
        $client->startActions();
        $this->assertEquals(1, $count);
    }

    /**
     * We expect no photos to be loaded in basic scenario with date limits
     *
     * @throws TGException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_basic_scenario_with_date_limit_none(): void {
        $count = 0;
        $saveHandler = function (PictureModel $model, int $id) use (&$count) {
            $count++;
        };
        $file = file_get_contents(__DIR__.self::DEFAULT_TRACE_PATH);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosClient(
            DateParser::parse('20190101'),
            DateParser::parse('20190202'),
            $saveHandler,
            $testGenerator
        );
        $client->startActions();
        $this->assertEquals(0, $count);
    }

    /**
     * We expect one photo to be loaded in basic scenario with date limits
     *
     * @throws TGException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_basic_scenario_with_date_limit_one(): void {
        $count = 0;
        $saveHandler = function (PictureModel $model, int $id) use (&$count) {
            $count++;
        };
        $file = file_get_contents(__DIR__.self::DEFAULT_TRACE_PATH);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosClient(
            DateParser::parse('20190101'),
            DateParser::parse('20200202'),
            $saveHandler,
            $testGenerator
        );
        $client->startActions();
        $this->assertEquals(1, $count);
    }

    /**
     * We expect one photo to be loaded in basic scenario with date limits and another date format
     *
     * @throws TGException
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_basic_scenario_with_date_limit_advanced(): void {
        $count = 0;
        $saveHandler = function (PictureModel $model, int $id) use (&$count) {
            $count++;
        };
        $file = file_get_contents(__DIR__.self::DEFAULT_TRACE_PATH);
        $basicGenerator = new NullBasicClientGenerator(json_decode($file, true));
        $authKey = self::DEFAULT_AUTHKEY;
        $testGenerator = new TestClientGenerator($basicGenerator, $authKey);
        $client = new GroupPhotosClient(
            DateParser::parse('2019-01-01 00:00:00'),
            DateParser::parse('20200202'),
            $saveHandler,
            $testGenerator
        );
        $client->startActions();
        $this->assertEquals(1, $count);
    }
}
