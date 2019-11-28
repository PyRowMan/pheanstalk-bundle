<?php

namespace Pyrowman\PheanstalkBundle\Proxy;

use Pheanstalk\Command\CreateScheduleCommand;
use Pheanstalk\Command\GetWorkflowInstancesCommand;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Structure\Schedule;
use Pheanstalk\Structure\TaskInstance;
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
        $this->dispatch(new CommandEvent($this, ['workflow' => $workflow]), CommandEvent::DELETE);

        $this->pheanstalk->delete($workflow);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function workflowExists($name)
    {
        $this->dispatch(new CommandEvent($this, ['name' => $name]), CommandEvent::WORKFLOW_EXISTS);

        return $this->pheanstalk->workflowExists($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkflow(Workflow $workflow)
    {
        $this->dispatch(new CommandEvent($this, ['workflow' => $workflow]), CommandEvent::TASK_EXISTS);

        return $this->pheanstalk->getWorkflow($workflow);
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkflowInstances(?Workflow $workflow = null, ?string $status = null)
    {
        $this->dispatch(new CommandEvent($this, [
            'workflow'  => $workflow,
            'status'    => $status
        ]), CommandEvent::WORKFLOW_INSTANCES);


        return $this->pheanstalk->getWorkflowInstances($workflow, $status);
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkflowInstancesDetails(WorkflowInstance $workflowInstance)
    {
        $this->dispatch(new CommandEvent($this, ['workflowInstance'  => $workflowInstance]),
            CommandEvent::WORKFLOW_INSTANCES_DETAILS);

        return $this->pheanstalk->getWorkflowInstancesDetails($workflowInstance);
    }

    /**
     * {@inheritDoc}
     */
    public function tubeExists($name)
    {
        $this->dispatch(new CommandEvent($this, ['name' => $name]), CommandEvent::TUBE_EXISTS);

        return $this->pheanstalk->tubeExists($name);
    }

    /**
     * {@inheritDoc}
     */
    public function listTubes()
    {
        $this->dispatch(new CommandEvent($this), CommandEvent::LIST_TUBES);

        return $this->pheanstalk->listTubes();
    }

    /**
     * {@inheritDoc}
     */
    public function peek()
    {
        $this->dispatch(new CommandEvent($this), CommandEvent::PEEK);

        return $this->pheanstalk->peek();
    }

    /**
     * {@inheritDoc}
     */
    public function put(Workflow $workflow)
    {
        $this->dispatch(new CommandEvent($this, ['workflow' => $workflow]), CommandEvent::PUT);

        return $this->pheanstalk->put($workflow);
    }

    /**
     * {@inheritDoc}
     */
    public function statsTube(Tube $tube)
    {
        $this->dispatch(new CommandEvent($this, ['tube' => $tube]), CommandEvent::STATS_TUBE);

        return $this->pheanstalk->statsTube($tube);
    }

    /**
     * {@inheritDoc}
     */
    public function stats()
    {
        $this->dispatch(new CommandEvent($this), CommandEvent::STATS);

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
        $this->dispatch(new CommandEvent($this, ['workflow' => $workflow]), CommandEvent::CREATE_WORKFLOW);

        $workflow = $this->pheanstalk->create($workflow);
        return $workflow;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Workflow $workflow): Workflow
    {
        $this->dispatch(new CommandEvent($this, ['workflow' => $workflow]), CommandEvent::UPDATE_WORKFLOW);

        return $this->pheanstalk->update($workflow);
    }

    /**
     * {@inheritdoc}
     */
    public function createSchedule(Schedule $schedule)
    {
        $this->dispatch(new CommandEvent($this, [
                'schedule'  => $schedule,
            ]), CommandEvent::CREATE_SCHEDULE);

        $workflowSchedule = $this->pheanstalk->createSchedule($schedule);
        return $workflowSchedule;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSchedule(Schedule $schedule)
    {
            $this->dispatch(new CommandEvent($this, [
                'schedule'  => $schedule,
            ]), CommandEvent::DELETE_SCHEDULE);

        return $this->pheanstalk->deleteSchedule($schedule);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchedule(int $schedule)
    {
        $this->dispatch(new CommandEvent($this, [
                'schedule'  => $schedule,
            ]), CommandEvent::GET_SCHEDULE);

        return $this->pheanstalk->getSchedule($schedule);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSchedule(Schedule $schedule): Schedule
    {
        $this->dispatch(new CommandEvent($this, [
                'schedule'  => $schedule,
            ]), CommandEvent::UPDATE_SCHEDULE);

        return $this->pheanstalk->updateSchedule($schedule);
    }

    /**
     * {@inheritdoc}
     */
    public function listSchedules()
    {
        $this->dispatch(new CommandEvent($this, []), CommandEvent::LIST_SCHEDULE);

        return $this->pheanstalk->listSchedules();
    }

    /**
     * {@inheritdoc}
     */
    public function createTask(string $name, string $group, string $path, $queue = 'default', $useAgent = false, $user = null, $host = null, $comment = null): Workflow
    {
        $datas = [
            'name'      => $name,
            'group'     => $group,
            'path'      => $path,
            'queue'     => $queue,
            'useAgent'  => $useAgent,
            'user'      => $user,
            'host'      => $host,
            'comment'   => $comment
        ];
        $this->dispatch(new CommandEvent($this, $datas), CommandEvent::CREATE_TASK);


        return $this->pheanstalk->createTask($name, $group, $path, $queue, $useAgent, $user, $host, $comment);
    }

    /**
     * {@inheritdoc}
     */
    public function createTube(Tube $tube): Tube
    {
        $this->dispatch(new CommandEvent($this, ['tube' => $tube]), CommandEvent::CREATE_TUBE);

        return $this->pheanstalk->createTube($tube);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTube(Tube $tube): Tube
    {
        $this->dispatch(new CommandEvent($this, ['tube' => $tube]), CommandEvent::UPDATE_TUBE);

        return $this->pheanstalk->updateTube($tube);
    }

    /**
     * {@inheritdoc}
     */
    public function cancel(WorkflowInstance $workflowInstance)
    {
        $this->dispatch(new CommandEvent($this, ['workflowInstance' => $workflowInstance]), CommandEvent::CANCEL);

        return $this->pheanstalk->cancel($workflowInstance);
    }

    public function kill(WorkflowInstance $workflowInstance, TaskInstance $taskInstance)
    {
        $this->dispatch(new CommandEvent($this, ['workflowInstance' => $workflowInstance, 'taskInstance' => $taskInstance]), CommandEvent::CANCEL);

        return $this->pheanstalk->kill($workflowInstance, $taskInstance);
    }

    /**
     * @param CommandEvent $commandEvent
     * @param string|null  $eventName
     */
    protected function dispatch(CommandEvent $commandEvent, string $eventName = null)
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch($commandEvent, $eventName);
        }
    }
}
