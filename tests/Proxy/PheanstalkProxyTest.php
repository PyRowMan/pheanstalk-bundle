<?php

namespace Pyrowman\PheanstalkBundle\Tests\Proxy;

use Doctrine\Common\Collections\ArrayCollection;
use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Structure\Schedule;
use Pheanstalk\Structure\TaskInstance;
use Pheanstalk\Structure\TimeSchedule;
use Pheanstalk\Structure\Tube;
use Pheanstalk\Structure\Workflow;
use Pheanstalk\Structure\WorkflowInstance;
use PHPUnit\Framework\TestCase;
use Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxy;
use Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxyInterface;
use Pheanstalk\PheanstalkInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PheanstalkProxyTest extends TestCase
{
    /**
     * @var PheanstalkProxyInterface
     */
    protected $pheanstalkProxy;

    /**
     * @var PheanstalkInterface
     */
    protected $pheanstalk;

    public function setUp()
    {
        $this->pheanstalk      = $this->getMockForAbstractClass(PheanstalkInterface::class);
        $this->pheanstalkProxy = new PheanstalkProxy();
    }

    public function tearDown()
    {
        unset($this->pheanstalk);
        unset($this->pheanstalkProxy);
    }

    public function testInterfaces()
    {
        $this->assertInstanceOf(PheanstalkProxyInterface::class, $this->pheanstalkProxy);
        $this->assertInstanceOf(PheanstalkInterface::class, $this->pheanstalkProxy);
    }

    public function testProxyValue()
    {
        $this->pheanstalkProxy->setPheanstalk($this->pheanstalk);
        $this->assertEquals($this->pheanstalk, $this->pheanstalkProxy->getPheanstalk());
    }

    public function namedFunctions()
    {
        $workflow = new Workflow('testName', 'testGroup', new ArrayCollection([]));
        $workflowInstance = new WorkflowInstance([]);
        $taskInstance = new TaskInstance([]);
        $tube = new Tube('testTube', 1);
        $schedule = new Schedule(1, new TimeSchedule());
        $connection = new Connection('localhost');
        return [
            ['setConnection', [$connection]],
            ['getConnection'],
            ['stats'],
            ['workflowExists', ['test']],
            ['getWorkflow', [$workflow]],
            ['getWorkflowInstances'],
            ['getWorkflowInstancesDetails', [$workflowInstance]],
            ['tubeExists', ['test']],
            ['listTubes'],
            ['peek'],
            ['put', [$workflow]],
            ['delete', [$workflow]],
            ['statsTube', [$tube]],
            ['stats'],
            ['create', [$workflow]],
            ['create', [$workflow, true]],
            ['update', [$workflow]],
            ['createSchedule', [$schedule]],
            ['deleteSchedule', [$schedule]],
            ['getSchedule', [1]],
            ['updateSchedule', [$schedule]],
            ['listSchedules'],
            ['createTask', ['testName', 'testGroup', 'bin/console c:c']],
            ['createTube', [$tube]],
            ['updateTube', [$tube]],
            ['cancel', [$workflowInstance]],
            ['kill', [$workflowInstance, $taskInstance]],
        ];
    }

    /**
     * @dataProvider namedFunctions
     */
    public function testProxyFunctionCalls($name, $value = null)
    {
        if (null === $value) {
            $value = [];
        }

        $pheanstalkProxy = new PheanstalkProxy();
        $pheanstalkMock  = $this->getMockForAbstractClass(PheanstalkInterface::class);
        $dispatchMock    = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $pheanstalkMock->expects($this->atLeastOnce())->method($name);

        $pheanstalkProxy->setPheanstalk($pheanstalkMock);
        $pheanstalkProxy->setDispatcher($dispatchMock);

        call_user_func_array([$pheanstalkProxy, $name], $value);
    }

    public function testCurrentClass()
    {
        $pheanstalk = new Pheanstalk('localhost');
        $this->pheanstalkProxy->setCurrentClass($pheanstalk);
        $this->assertSame($pheanstalk, $this->pheanstalkProxy->getCurrentClass());
    }

    public function testName()
    {
        $this->pheanstalkProxy->setName('test');
        $this->assertSame('test', $this->pheanstalkProxy->getName());
    }

    public function testDispatcher()
    {
        $dispatcher = new EventDispatcher();
        $this->pheanstalkProxy->setDispatcher($dispatcher);
        $this->assertSame($dispatcher, $this->pheanstalkProxy->getDispatcher());
    }
}
