<?php

declare(strict_types=1);

namespace Integration\Scenario;

use Helpers\NullBasicClientGenerator;
use Helpers\TestClientGenerator;
use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Scenario\UserContactsScenario;

class UserContactsTest extends TestCase
{
    private const DEFAULT_AUTHKEY = '77476991876:7b22646576696365223a226875617765695354462d414c3130222c22616e64726f696453646b56657273696f6e223a2253444b203237222c2266697273744e616d65223a224b6972616e222c226c6173744e616d65223a224b656e6e79222c226465766963654c616e67223a22656e2d7573222c226170704c616e67223a22656e222c2261707056657273696f6e223a22342e392e31222c2261707056657273696f6e436f6465223a223133363137222c226c6179657256657273696f6e223a38357d:Tz/zv6i70SsFHsKvvkKs6VYeb8OUDC0zQSn8lEkfBeD2Un3hey/BcM5UeT+5NbIiW3Ioy0BqoluLGViG6comBiCdKiYDHeNAgv8CuiqsVwI1uQXIEM6kIKA5SJOmc+mDIEy2hxuAfFVpuNL3cBKicwQ4YcofdEh/na7W/IUt5AcwBpI//Gco6JjjD4zhwGretLslmMooeADlaO0f2+1J+7qjXTTen3FT6ozjYaGyIIJeGtX8Qnjqva60pBkTAor1t1E5eghpJVTuOzZK/5eAoVyl9JG7g5kFfPQGQ70mIuQFkgpZ7MhD0Jqvm4H/GcAoQd9iNqXFVMYWl298GM7qBQ==:7b2263726561746564223a313533393638363236332c226170695f6964223a362c2264635f6964223a322c2264635f6970223a223134392e3135342e3136372e3530222c2264635f706f7274223a3434337d';
    private const TRACE_PATH = '/traces/user-contacts-scenario.txt';
    private const TIMEOUT = 0.25;

    /**
     * @throws TGException
     */
    public function test_get_users_first_name(): void
    {
        $file = file_get_contents(__DIR__.self::TRACE_PATH);
        $baseGenerator = new NullBasicClientGenerator(json_decode($file, true));
        $generator = new TestClientGenerator($baseGenerator, self::DEFAULT_AUTHKEY);

        $numbers_parsed = 0;

        $callback = function (UserInfoModel $model) use (&$numbers_parsed) {
            if ($model) {
                $numbers_parsed++;

                switch ($model->phone){
                    case '79857877977':
                        $this->assertEquals('Andrey', $model->firstName);
                        break;
                    case '79859947294':
                        $this->assertEquals('Ирина', $model->firstName);
                        break;
                    case '79867241777':
                        $this->assertEquals('S$', $model->firstName);
                        break;
                    default:
                        break;
                }
            }
        };
        $numbers = [
            '79859947294',
            '79857877977',
            '79867241777',
        ];

        $scenario = new UserContactsScenario(
            $numbers,
            $callback,
            $generator
        );
        $scenario->startActions();

        $this->assertEquals(3, $numbers_parsed);
    }
}
