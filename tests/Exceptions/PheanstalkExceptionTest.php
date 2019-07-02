<?php

namespace Pyrowman\PheanstalkBundle\Tests\Exception;

use Pyrowman\PheanstalkBundle\Exceptions\PheanstalkException;

class PheanstalkExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $exception = new PheanstalkException();

        $this->assertEquals('Pheanstalk exception.', $exception->getMessage());
    }
}
