<?php

namespace Pyrowman\PheanstalkBundle\Event;

use Pheanstalk\PheanstalkInterface;
use Symfony\Contracts\EventDispatcher\Event;

class CommandEvent extends Event
{
    const DELETE                        = 'leezy.pheanstalk.event.delete';
    const WORKFLOW_EXISTS               = 'leezy.pheanstalk.event.workflow_exists';
    const TASK_EXISTS                   = 'leezy.pheanstalk.event.task_exists';
    const TUBE_EXISTS                   = 'leezy.pheanstalk.event.tube_exists';
    const LIST_TUBES                    = 'leezy.pheanstalk.event.list_tubes';
    const LIST_WORKFLOWS                = 'leezy.pheanstalk.event.list_workflows';
    const PEEK                          = 'leezy.pheanstalk.event.peek';
    const PEEK_READY                    = 'leezy.pheanstalk.event.peek_ready';
    const PUT                           = 'leezy.pheanstalk.event.put';
    const STATS                         = 'leezy.pheanstalk.event.stats';
    const STATS_TUBE                    = 'leezy.pheanstalk.event.stats_tube';
    const STATS_JOB                     = 'leezy.pheanstalk.event.stats_job';
    const CREATE_WORKFLOW               = 'leezy.pheanstalk.event.create_workflow';
    const CREATE_WORKFLOW_SCHEDULER     = 'leezy.pheanstalk.event.create_workflow_scheduler';
    const CREATE_TASK                   = 'leezy.pheanstalk.event.create_task';
    const CREATE_TUBE                   = 'leezy.pheanstalk.event.create_tube';

    /**
     * @var PheanstalkInterface
     */
    private $pheanstalk;

    /**
     * @var array
     */
    private $payload;

    /**
     * @param PheanstalkInterface $pheanstalk
     * @param array               $payload
     */
    public function __construct(PheanstalkInterface $pheanstalk, array $payload = [])
    {
        $this->pheanstalk = $pheanstalk;
        $this->payload    = $payload;
    }

    /**
     * @return PheanstalkInterface
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
