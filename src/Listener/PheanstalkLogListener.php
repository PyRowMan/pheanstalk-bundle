<?php

namespace Pyrowman\PheanstalkBundle\Listener;

use Pyrowman\PheanstalkBundle\Event\CommandEvent;
use Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxyInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PheanstalkLogListener implements EventSubscriberInterface
{
    use LoggerAwareTrait;

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CommandEvent::DELETE                        => 'onCommand',
            CommandEvent::LIST_TUBES                    => 'onCommand',
            CommandEvent::LIST_WORKFLOWS                => 'onCommand',
            CommandEvent::PEEK                          => 'onCommand',
            CommandEvent::PUT                           => 'onCommand',
            CommandEvent::STATS                         => 'onCommand',
            CommandEvent::STATS_TUBE                    => 'onCommand',
            CommandEvent::STATS_JOB                     => 'onCommand',
            CommandEvent::CREATE_TASK                   => 'onCommand',
            CommandEvent::CREATE_TUBE                   => 'onCommand',
            CommandEvent::CREATE_WORKFLOW               => 'onCommand',
            CommandEvent::UPDATE_WORKFLOW               => 'onCommand',
            CommandEvent::CREATE_WORKFLOW_SCHEDULER     => 'onCommand',
            CommandEvent::WORKFLOW_EXISTS               => 'onCommand',
            CommandEvent::WORKFLOW_INSTANCES            => 'onCommand',
            CommandEvent::WORKFLOW_INSTANCES_DETAILS    => 'onCommand',
            CommandEvent::TASK_EXISTS                   => 'onCommand',
            CommandEvent::TUBE_EXISTS                   => 'onCommand',
            CommandEvent::CANCEL                        => 'onCommand',
            CommandEvent::KILL                          => 'onCommand',
            CommandEvent::CREATE_SCHEDULE               => 'onCommand',
            CommandEvent::UPDATE_SCHEDULE               => 'onCommand',
            CommandEvent::LIST_SCHEDULE                 => 'onCommand',
            CommandEvent::DELETE_SCHEDULE               => 'onCommand',
            CommandEvent::GET_SCHEDULE                  => 'onCommand',
        ];
    }

    /**
     * @param CommandEvent $event
     * @param string       $eventName
     */
    public function onCommand(CommandEvent $event, $eventName)
    {
        if (!$this->logger) {
            return;
        }

        $pheanstalk = $event->getPheanstalk();
        $connection = $pheanstalk->getConnection();

        if (!$connection->isServiceListening()) {
            $this->logger->warning('Pheanstalk connection isn\'t listening');
        }

        $pheanstalkName = 'unknown';
        if ($pheanstalk instanceof PheanstalkProxyInterface) {
            $pheanstalkName = $pheanstalk->getName();
        }

        $nameExploded = explode('.', $eventName);

        $this->logger->info(
            'Pheanstalk command: '.$nameExploded[count($nameExploded) - 1],
            [
                'payload'    => $event->getPayload(),
                'pheanstalk' => $pheanstalkName,
            ]
        );
    }
}
