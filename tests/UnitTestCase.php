<?php


namespace HecFranco\PasswordPolicyBundle\Tests;


use Mockery;
use DateTime;
use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{

    protected function tearDown(): void
    {
        Mockery::close();
    }

    protected function randomDateTime(int $startDate = 0, int $endDate = PHP_INT_MAX): DateTime
    {
        $timestamp = random_int($startDate, $endDate);

        return (new DateTime())->setTimestamp($timestamp);
    }

}
