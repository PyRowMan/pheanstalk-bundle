<?php

namespace Pyrowman\PheanstalkBundle\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Pheanstalk\Exception\ServerException;
use Pheanstalk\Structure\Tube;
use PHPUnit\Framework\TestCase;
use Pyrowman\PheanstalkBundle\DataCollector\PheanstalkDataCollector;
use Pyrowman\PheanstalkBundle\PheanstalkLocator;
use Pheanstalk\Connection;
use Pheanstalk\PheanstalkInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PheanstalkDataCollectorTest extends TestCase
{
    public function testCollectOnConnectionClose()
    {
        $pheanstalkConnection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pheanstalkConnection
            ->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue('127.0.0.1'));

        $pheanstalkConnection
            ->expects($this->any())
            ->method('getPort')
            ->will($this->returnValue('5000'));

        $pheanstalkConnection
            ->expects($this->any())
            ->method('getConnectTimeout')
            ->will($this->returnValue(60));

        $pheanstalkConnection
            ->expects($this->any())
            ->method('isServiceListening')
            ->will($this->returnValue(false));

        $pheanstalkA = $this->getMockForAbstractClass(PheanstalkInterface::class);
        $pheanstalkA->expects($this->any())->method('getConnection')->will($this->returnValue($pheanstalkConnection));
        $pheanstalkLocator = new PheanstalkLocator();
        $pheanstalkLocator->addPheanstalk('default', $pheanstalkA, true);

        $request  = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->getMock();
        $dataCollector = new PheanstalkDataCollector($pheanstalkLocator);
        $dataCollector->collect($request, $response);
        $data = $dataCollector->getPheanstalks();
        $this->assertArrayHasKey('name', $data['default']);
        $this->assertArrayHasKey('host', $data['default']);
        $this->assertArrayHasKey('port', $data['default']);
        $this->assertArrayHasKey('timeout', $data['default']);
        $this->assertArrayHasKey('default', $data['default']);
        $this->assertArrayHasKey('stats', $data['default']);
        $this->assertArrayHasKey('listening', $data['default']);
        $this->assertEmpty($dataCollector->getTubes());
        $this->assertSame(0, $dataCollector->getJobCount());
        $this->assertEmpty($dataCollector->getJobs());
        $this->assertSame('pheanstalk', $dataCollector->getName());
    }

    public function testCollect()
    {
        $pheanstalkConnection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pheanstalkConnection
            ->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue('127.0.0.1'));

        $pheanstalkConnection
            ->expects($this->any())
            ->method('getPort')
            ->will($this->returnValue('5000'));

        $pheanstalkConnection
            ->expects($this->any())
            ->method('getConnectTimeout')
            ->will($this->returnValue(60));

        $pheanstalkConnection
            ->expects($this->any())
            ->method('isServiceListening')
            ->will($this->returnValue(true));

        $pheanstalkA = $this->getMockForAbstractClass(PheanstalkInterface::class);
        $pheanstalkB = $this->getMockForAbstractClass(PheanstalkInterface::class);

        $pheanstalkA->expects($this->any())->method('getConnection')->will($this->returnValue($pheanstalkConnection));
        $pheanstalkB->expects($this->any())->method('getConnection')->will($this->returnValue($pheanstalkConnection));

        $pheanstalkLocator = new PheanstalkLocator();
        $pheanstalkLocator->addPheanstalk('default', $pheanstalkA, true);
        $pheanstalkLocator->addPheanstalk('foo', $pheanstalkB);

        $request  = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->getMock();

        $dataCollector = new PheanstalkDataCollector($pheanstalkLocator);
        $tubeA = new Tube('euuuh', 1);
        $tubeB = new Tube('default@', 1);
        $pheanstalkA->expects($this->any())->method('listTubes')->willReturn(new ArrayCollection([$tubeA]));
        $pheanstalkB->expects($this->any())->method('listTubes')->willReturn(new ArrayCollection([$tubeB]));
        $pheanstalkA->expects($this->any())->method('peek')->willReturn([1 => ['id' => 1]]);
        $pheanstalkB->expects($this->any())->method('peek')->willThrowException(new ServerException('testPeekException'));
        $dataCollector->collect($request, $response);

        $this->assertArrayHasKey('default', $dataCollector->getPheanstalks());
        $this->assertArrayHasKey('foo', $dataCollector->getPheanstalks());
        $this->assertArrayNotHasKey('bar', $dataCollector->getPheanstalks());

        $data = $dataCollector->getPheanstalks();
        $tubes = $dataCollector->getTubes();
        $jobs = $dataCollector->getJobs();

        $this->assertArrayHasKey('name', $data['default']);
        $this->assertArrayHasKey('host', $data['default']);
        $this->assertArrayHasKey('port', $data['default']);
        $this->assertArrayHasKey('timeout', $data['default']);
        $this->assertArrayHasKey('default', $data['default']);
        $this->assertArrayHasKey('stats', $data['default']);
        $this->assertArrayHasKey('listening', $data['default']);
        $this->assertSame(2, count($tubes));
        $this->assertSame(1, $jobs['ready']['id']);
    }
}
