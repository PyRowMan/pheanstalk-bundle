<?php

namespace Pyrowman\PheanstalkBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Pyrowman\PheanstalkBundle\Event\CommandEvent;
use Pheanstalk\PheanstalkInterface;

class CommandEventTest extends TestCase
{
    public function testCommandEvent()
    {
        $pheanstalk = $this->getMockForAbstractClass(PheanstalkInterface::class);
        $payload = ['foo'];

        $event = new CommandEvent($pheanstalk, $payload);

        $this->assertSame($pheanstalk, $event->getPheanstalk());
        $this->assertSame($payload, $event->getPayload());
    }
}
