<?php

use Client\AuthKey\AuthKeyCreator;
use Client\AuthKey\Versions\AuthKey_v0_RawB64;
use Client\AuthKey\Versions\AuthKey_v1_Extended;
use Client\AuthKey\Versions\AuthKey_v1_Simple;
use Client\AuthKey\Versions\AuthKey_v2;
use Client\AuthKey\Versions\AuthKey_v2_Authorized;
use Client\AuthKey\Versions\AuthKey_v2_Phone;
use PHPUnit\Framework\TestCase;

class AuthKeyCreatorTest extends TestCase
{

    public function test_create_authkey_v2_authorized()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $authKey = AuthKeyCreator::createFromString('79803100357:7b22646576696365223a2273616d73756e6747542d4e38303030222c22616e64726f696453646b56657273696f6e223a2253444b203231222c2266697273744e616d65223a226e616d653538666630303735222c226c6173744e616d65223a226c6173746e616d656264623634623966222c226465766963654c616e67223a2272752d7275222c226170704c616e67223a227275222c2261707056657273696f6e223a22342e382e3131222c2261707056657273696f6e436f6465223a223133313831222c226c6179657256657273696f6e223a38327d:XrYgWUps5khLnDLE/5c9buuAMLQsIqjv8WyPriN0bZ1ePREdBPPfdbc0W+Fvr+KKRsg8lm+D8mvoe/tcwQ9SX1hyGqqu0Qc7HtRr9Y+OI1UL47CH/UdMDhaeMMdPIulMGrTLJJJW0bG3IFnLC+5hUkk7gH90agg4WGzNjBgz3e90aZ3nsgovefrQLT2549aklGOW3+rbXBuID3iIOLu+A1hafuwqhS3Z3TGi1AuYqZcGxDzZVOn9OlFjVBv2/c+VeqiwTDqwg5Pq79edMPBxluOrXUOEaZDBXcqznDwk1lJ7zhgX/cHHU9isrgdzO4qzt+gNZ71ybYx1JxRrx6P9/A==:7b2263726561746564223a313533323434373530342c226170695f6964223a362c2264635f6964223a322c2264635f6970223a223134392e3135342e3136372e3530222c2264635f706f7274223a3434337d');
        $this->assertTrue($authKey instanceof AuthKey_v2_Authorized);
    }


    public function test_create_authkey_v2_phone()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $authKey = AuthKeyCreator::createFromString('79264598639:QGcc2CwGdV45xdA8XMt+b61zMvStAPdl2hbEEpSo+IN5NZpwlahCCRhV2uZrwpDCSdNDW48SbzC1vjx0If+aCFx9lCBm/QnInvf+2ByGvxRBlH21JtVSmttw+4I0zDuRlnxv8/i6oAD/m4RfpFKwPFWoB0pCDhsrI610J3s2C3FB672qKFprAxABjtsWO/DeeHyzgc/0SYlDfwdVCj3vj8qQf3hb4cOGShmI3KjA2igzAMJ0yeJQBU/wUvhH52rQkbmsIwgUKvzy9DuFByYvUtmIoC5r18FZLo34AJHOVCqqF0CVEjpVna/O1WPZZjUgcKDNxQb7L4tYhYJx3RKcEg==:7b2263726561746564223a313533313832323236352c2276657273696f6e223a342c226170695f6964223a367d');
        $this->assertTrue($authKey instanceof AuthKey_v2_Phone);
    }


    public function test_create_authkey_v2()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $authKey = AuthKeyCreator::createFromString('QGcc2CwGdV45xdA8XMt+b61zMvStAPdl2hbEEpSo+IN5NZpwlahCCRhV2uZrwpDCSdNDW48SbzC1vjx0If+aCFx9lCBm/QnInvf+2ByGvxRBlH21JtVSmttw+4I0zDuRlnxv8/i6oAD/m4RfpFKwPFWoB0pCDhsrI610J3s2C3FB672qKFprAxABjtsWO/DeeHyzgc/0SYlDfwdVCj3vj8qQf3hb4cOGShmI3KjA2igzAMJ0yeJQBU/wUvhH52rQkbmsIwgUKvzy9DuFByYvUtmIoC5r18FZLo34AJHOVCqqF0CVEjpVna/O1WPZZjUgcKDNxQb7L4tYhYJx3RKcEg==:7b2263726561746564223a313533313832323236352c2276657273696f6e223a342c226170695f6964223a367d');
        $this->assertTrue($authKey instanceof AuthKey_v2);
    }


    public function test_create_authkey_v1_extended()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $authKey = AuthKeyCreator::createFromString('79103967303:EKjPEqDsu3O0+4afKGRYe3aO5BGnpExhpnWbvj+6c2HiOxxCPGlD5Eg9H4t1ub4X/W6EjGJhBvo7GaYQTbi7CIP5wRzzMdMuNdqVAXhrt3bTYVw3h6hfbBM0FMvyCuNmbTQyfhEH2K1aPUbJ708jHftgEqrbM/zorpxCh9f4pmCXz5qQy7nf+A9LshYruu0aFmya2+ndI2IhAZMww+PAy5ex0BihbiseNEuSeA4Y002uBrMFCsNtkxlsNCqkgJikLRHyxct85M94vDkLxR3ZVXIfUyq/8CQNR1O/FO+AdqYHxmuXFL+sNMgwGX04STDTafG48v7cdiVyh/8b5zTHi2h+ljjsuBVt');
        $this->assertTrue($authKey instanceof AuthKey_v1_Extended);
    }


    public function test_create_authkey_v1_simple()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $authKey = AuthKeyCreator::createFromString('1WE+hF/cA/qxQ20F6eD1WLc7xiUNWdJqkpf3VKLt+4kZAsc4MdU+d/PKfVANymRuk8AWfOYAyF3HU7Aqg8NWyEyjtsrpIfx8m3MJ/WAtC5o0NqSnlWxLtO/6ZJrAMishS1WWuOEToz08g/r/pfOIZT1PiPy1lyCX/S1x4n0MWthnHYUgHazwP9gNag132w+OrkB7uJ3Scu9PfSbh4U5+h3pw0/jiO3Yf6b7XVbNklsf1W9s1qDCMyaZaFXh3YrojaV3mbFnnp9grxHDLHkWoGHelOpx6RGsosL/p6j/TkUNEHd50+3HPERPjbQr55GYp2fQxga25JzjEhcy9XoqQaYuJVEx5UPEn');
        $this->assertTrue($authKey instanceof AuthKey_v1_Simple);
    }


    public function test_create_authkey_v0()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $authKey = AuthKeyCreator::createFromString('SevnGqpSHuyxEqIU+y3np4gVnU3D3dix/RtahVp8wnYx04+76lpGs9n7vcKWEUlWpFYEkpJYj2YpFahmq98wW7ZY7ELhVHoJ0g1ZlfbkYRTplj34WX4zGUptScrMcTUoUdhrZope6Ae9HEs2IS66Vr8Gms8fjVkWihEUokPERJ1Umv0E8/tayc0ssCSHwE4CUjC6G6Dx/3uByOsZfcmUDj3RaeKkKwy84x5VLjrp9N4jWYbSRgd9orj2Co8KkmAdYqI6GUFbpgPZvGBGAZXT/E9oEDNO/geYKutPh0rLpW9JedftOWpQbmyAIKCZHNrX3NCp0JUYU/+Y9H/p7iyOEQ==');
        $this->assertTrue($authKey instanceof AuthKey_v0_RawB64);
    }

}
