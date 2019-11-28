<?php

namespace Pyrowman\PheanstalkBundle\Event;

use Pheanstalk\PheanstalkInterface;
use Symfony\Contracts\EventDispatcher\Event;

class CommandEvent extends Event
{
    const DELETE                        = 'pheanstalk.event.delete';
    const WORKFLOW_EXISTS               = 'pheanstalk.event.workflow_exists';
    const WORKFLOW_INSTANCES            = 'pheanstalk.event.workflow_instances';
    const WORKFLOW_INSTANCES_DETAILS    = 'pheanstalk.event.workflow_instances_details';
    const TASK_EXISTS                   = 'pheanstalk.event.task_exists';
    const TUBE_EXISTS                   = 'pheanstalk.event.tube_exists';
    const LIST_TUBES                    = 'pheanstalk.event.list_tubes';
    const LIST_WORKFLOWS                = 'pheanstalk.event.list_workflows';
    const PEEK                          = 'pheanstalk.event.peek';
    const PEEK_READY                    = 'pheanstalk.event.peek_ready';
    const PUT                           = 'pheanstalk.event.put';
    const CANCEL                        = 'pheanstalk.event.cancel';
    const KILL                          = 'pheanstalk.event.kill';
    const STATS                         = 'pheanstalk.event.stats';
    const STATS_TUBE                    = 'pheanstalk.event.stats_tube';
    const STATS_JOB                     = 'pheanstalk.event.stats_job';
    const CREATE_WORKFLOW               = 'pheanstalk.event.create_workflow';
    const CREATE_SCHEDULE               = 'pheanstalk.event.create_schedule';
    const UPDATE_SCHEDULE               = 'pheanstalk.event.create_schedule';
    const DELETE_SCHEDULE               = 'pheanstalk.event.delete_schedule';
    const LIST_SCHEDULE                 = 'pheanstalk.event.list_schedule';
    const GET_SCHEDULE                  = 'pheanstalk.event.get_schedule';
    const UPDATE_WORKFLOW               = 'pheanstalk.event.update_workflow';
    const CREATE_WORKFLOW_SCHEDULER     = 'pheanstalk.event.create_workflow_scheduler';
    const CREATE_TASK                   = 'pheanstalk.event.create_task';
    const CREATE_TUBE                   = 'pheanstalk.event.create_tube';
    const UPDATE_TUBE                   = 'pheanstalk.event.update_tube';

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
