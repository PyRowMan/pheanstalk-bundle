<?php

namespace Pyrowman\PheanstalkBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Pyrowman\PheanstalkBundle\Exceptions\PheanstalkException;

class PheanstalkExceptionTest extends TestCase
{
    public function testConstructor()
    {
        $exception = new PheanstalkException();

        $this->assertEquals('Pheanstalk exception.', $exception->getMessage());
    }
}
