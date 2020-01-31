<?php

declare(strict_types=1);

namespace Unit\Helpers;

use Helpers\DateParser;
use PHPUnit\Framework\TestCase;

class DateParserTest extends TestCase
{
    /**
     * YYYYmmdd
     */
    public function test_fmt1(): void
    {
        $v = DateParser::parse('20190101');
        $this->assertEquals(1546300800, $v);
    }

    /**
     * YYYYmmdd HH:ii:ss
     */
    public function test_fmt2(): void
    {
        $v = DateParser::parse('20190101 00:00:00');
        $this->assertEquals(1546300800, $v);
    }

    /**
     * YYYY-mm-dd HH:ii:ss
     */
    public function test_fmt3(): void
    {
        $v = DateParser::parse('20190101 00:00:00');
        $this->assertEquals(1546300800, $v);
    }
}
