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
            CommandEvent::CREATE_WORKFLOW_SCHEDULER     => 'onCommand',
            CommandEvent::WORKFLOW_EXISTS               => 'onCommand',
            CommandEvent::TUBE_EXISTS                   => 'onCommand',
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
