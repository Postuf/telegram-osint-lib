<?php

declare(strict_types=1);

namespace Integration\Scenario;

use Helpers\NullBasicClientGenerator;
use Helpers\TestClientGenerator;
use Helpers\TraceConverter\TraceConverterJsonToText;
use JsonException;
use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\InfoObtainingClient\Models\GeoChannelModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Scenario\GeoSearchScenario;
use TelegramOSINT\Scenario\GroupMembersScenario;

class GeoSearchTest extends TestCase
{
    private const DEFAULT_AUTHKEY = '79803100357:7b22646576696365223a2273616d73756e6747542d4e38303030222c22616e64726f696453646b56657273696f6e223a2253444b203231222c2266697273744e616d65223a226e616d653538666630303735222c226c6173744e616d65223a226c6173746e616d656264623634623966222c226465766963654c616e67223a2272752d7275222c226170704c616e67223a227275222c2261707056657273696f6e223a22342e382e3131222c2261707056657273696f6e436f6465223a223133313831222c226c6179657256657273696f6e223a38327d:XrYgWUps5khLnDLE/5c9buuAMLQsIqjv8WyPriN0bZ1ePREdBPPfdbc0W+Fvr+KKRsg8lm+D8mvoe/tcwQ9SX1hyGqqu0Qc7HtRr9Y+OI1UL47CH/UdMDhaeMMdPIulMGrTLJJJW0bG3IFnLC+5hUkk7gH90agg4WGzNjBgz3e90aZ3nsgovefrQLT2549aklGOW3+rbXBuID3iIOLu+A1hafuwqhS3Z3TGi1AuYqZcGxDzZVOn9OlFjVBv2/c+VeqiwTDqwg5Pq79edMPBxluOrXUOEaZDBXcqznDwk1lJ7zhgX/cHHU9isrgdzO4qzt+gNZ71ybYx1JxRrx6P9/A==:7b2263726561746564223a313533323434373530342c226170695f6964223a362c2264635f6964223a322c2264635f6970223a223134392e3135342e3136372e3530222c2264635f706f7274223a3434337d';
    private const TRACE_PATH = '/traces/trace-geo-user.json';
    private const TIMEOUT = 0.25;

    /**
     * php geoSearch.php 55.75639,37.661931 rostislav_u 5
     *
     * @throws TGException|JsonException
     */
    public function test_geo_search(): void
    {
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::TRACE_PATH);
        $baseGenerator = new NullBasicClientGenerator(json_decode($file, true, 512, JSON_THROW_ON_ERROR));
        $count = 0;
        $handler = static function ($arg = null) use (&$count) {
            if ($arg) {
                $count++;
            }
        };
        $generator = new TestClientGenerator($baseGenerator, self::DEFAULT_AUTHKEY);

        $finders = [];
        $username = 'rostislav_u';
        $groupHandler = static function (GeoChannelModel $model) use (&$generator, &$finders, $username, &$handler) {
            $membersFinder = new GroupMembersScenario(
                $model->getGroupId(),
                $handler,
                $generator,
                100,
                $username
            );
            $membersFinder->setTimeout(self::TIMEOUT);

            $membersFinder->startActions(false);
            $finders[] = $membersFinder;
        };
        $points = [[55.75393, 37.615714], [55.75639, 37.661931]];
        $search = new GeoSearchScenario($points, $groupHandler, $generator, 5);
        $search->setTimeout(self::TIMEOUT);
        $search->startActions();

        self::assertEquals(1, $count);

    }
}
