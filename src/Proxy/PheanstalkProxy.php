<?php

namespace Pyrowman\PheanstalkBundle\Proxy;

use Pheanstalk\Command\CreateScheduleCommand;
use Pheanstalk\Command\GetWorkflowInstancesCommand;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Structure\TimeSchedule;
use Pheanstalk\Structure\Tube;
use Pheanstalk\Structure\Workflow;
use Pheanstalk\Structure\WorkflowInstance;
use Pyrowman\PheanstalkBundle\Event\CommandEvent;
use Pheanstalk\Connection;
use Pheanstalk\PheanstalkInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PheanstalkProxy implements PheanstalkProxyInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Pheanstalk
     */
    protected $pheanstalk;

    /** @var $currentClass PheanstalkInterface */
    private $currentClass;

    /**
     * {@inheritDoc}
     */
    public function setConnection(Connection $connection)
    {
        $this->pheanstalk->setConnection($connection);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConnection()
    {
        return $this->pheanstalk->getConnection();
    }

    /**
     * @return PheanstalkInterface
     */
    public function getCurrentClass(): PheanstalkInterface
    {
        return $this->currentClass ?? $this;
    }

    /**
     * @param PheanstalkInterface $currentClass
     *
     * @return Pheanstalk
     */
    public function setCurrentClass(PheanstalkInterface $currentClass): PheanstalkInterface
    {
        $this->currentClass = $currentClass;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Workflow $workflow)
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, ['workflow' => $workflow]), CommandEvent::DELETE);
        }

        $this->pheanstalk->delete($workflow);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function workflowExists($name)
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, ['name' => $name]), CommandEvent::WORKFLOW_EXISTS);
        }

        return $this->pheanstalk->workflowExists($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkflow(Workflow $workflow)
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, ['workflow' => $workflow]), CommandEvent::TASK_EXISTS);
        }

        return $this->pheanstalk->getWorkflow($workflow);
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkflowInstances(?Workflow $workflow, string $status = GetWorkflowInstancesCommand::FILTER_EXECUTING)
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, [
                'workflow'  => $workflow,
                'status'    => $status
            ]), CommandEvent::WORKFLOW_INSTANCES);
        }

        return $this->pheanstalk->getWorkflowInstances($workflow, $status);
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkflowInstancesDetails(WorkflowInstance $workflowInstance)
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, ['workflowInstance'  => $workflowInstance]),
                CommandEvent::WORKFLOW_INSTANCES_DETAILS);
        }

        return $this->pheanstalk->getWorkflowInstancesDetails($workflowInstance);
    }

    /**
     * {@inheritDoc}
     */
    public function tubeExists($name)
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, ['name' => $name]), CommandEvent::TUBE_EXISTS);
        }

        return $this->pheanstalk->tubeExists($name);
    }

    /**
     * {@inheritDoc}
     */
    public function listTubes()
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this), CommandEvent::LIST_TUBES);
        }

        return $this->pheanstalk->listTubes();
    }

    /**
     * {@inheritDoc}
     */
    public function peek()
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this), CommandEvent::PEEK);
        }

        return $this->pheanstalk->peek();
    }

    /**
     * {@inheritDoc}
     */
    public function put(Workflow $workflow)
    {
        if ($this->dispatcher)
            $this->dispatcher->dispatch(new CommandEvent($this, ['workflow' => $workflow]), CommandEvent::PUT);

        return $this->pheanstalk->put($workflow);
    }

    /**
     * {@inheritDoc}
     */
    public function statsJob($job)
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(CommandEvent::STATS_JOB, new CommandEvent($this, ['job' => $job]));
        }

        return $this->pheanstalk->statsJob($job);
    }

    /**
     * {@inheritDoc}
     */
    public function statsTube($tube)
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, ['tube' => $tube]), CommandEvent::STATS_TUBE);
        }

        return $this->pheanstalk->statsTube($tube);
    }

    /**
     * {@inheritDoc}
     */
    public function stats()
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this), CommandEvent::STATS);
        }

        return $this->pheanstalk->stats();
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param EventDispatcherInterface $dispatch
     */
    public function setDispatcher(EventDispatcherInterface $dispatch)
    {
        $this->dispatcher = $dispatch;
    }

    /**
     * {@inheritDoc}
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }

    /**
     * {@inheritDoc}
     */
    public function setPheanstalk(PheanstalkInterface $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
        $this->pheanstalk->setCurrentClass($this);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Workflow $workflow, $force = false): Workflow
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, ['workflow' => $workflow]), CommandEvent::CREATE_WORKFLOW);
        }

        $workflow = $this->pheanstalk->create($workflow);
        return $workflow;
    }

    /**
     * {@inheritdoc}
     */
    public function createSchedule(Workflow $workflow, TimeSchedule $schedule, $onFailure = CreateScheduleCommand::FAILURE_TYPE_CONTINUE, $active = true, $comment = null)
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, [
                'workflow'  => $workflow,
                'schedule'  => $schedule,
                'onFailure' => $onFailure,
                'active'    => $active,
                'comment'   => $comment
            ]), CommandEvent::CREATE_WORKFLOW);
        }

        $workflowSchedule = $this->pheanstalk->createSchedule($workflow, $schedule, $onFailure, $active, $comment);
        return $workflowSchedule;
    }

    /**
     * {@inheritdoc}
     */
    public function createTask(string $name, string $group, string $path, $queue = 'default', $useAgent = false, $user = null, $host = null, $comment = null): Workflow
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, [
                'name'      => $name,
                'group'     => $group,
                'path'      => $path,
                'queue'     => $queue,
                'useAgent'  => $useAgent,
                'user'      => $user,
                'host'      => $host,
                'comment'   => $comment
            ]), CommandEvent::CREATE_TASK);
        }

        return $this->pheanstalk->createTask($name, $group, $path, $queue, $useAgent, $user, $host, $comment);
    }

    public function createTube(Tube $tube): Tube
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new CommandEvent($this, ['tube' => $tube]), COmmandEvent::CREATE_TUBE);
        }

        return $this->pheanstalk->createTube($tube);
    }
}
