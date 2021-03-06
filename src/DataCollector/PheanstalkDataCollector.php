<?php

namespace Pyrowman\PheanstalkBundle\DataCollector;

use Pheanstalk\Structure\Queue;
use Pheanstalk\Structure\Tube;
use Pyrowman\PheanstalkBundle\PheanstalkLocator;
use Pheanstalk\Exception\ServerException;
use Pheanstalk\PheanstalkInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * This is the data collector for PheanstalkBundle.
 *
 * @see    http://symfony.com/doc/current/cookbook/profiler/data_collector.html
 *
 * @author Maxime Aoustin <max44410@gmail.com>
 */
class PheanstalkDataCollector extends DataCollector
{
    /**
     * @var PheanstalkLocator
     */
    protected $pheanstalkLocator;

    /**
     * @param PheanstalkLocator $pheanstalkLocator
     */
    public function __construct(PheanstalkLocator $pheanstalkLocator)
    {
        $this->pheanstalkLocator = $pheanstalkLocator;
        $this->data              = [
            'pheanstalks' => [],
            'tubes'       => [],
            'jobCount'    => 0,
            'jobs'        => [],
        ];
    }

    public function reset()
    {
        $this->data = [
            'pheanstalks' => [],
            'tubes'       => [],
            'jobCount'    => 0,
            'jobs'        => [],
        ];
    }

    /**
     * @inheritdoc
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $this->reset();
        $defaultPheanstalk = $this->pheanstalkLocator->getDefaultPheanstalk();

        // Collect the information
        foreach ($this->pheanstalkLocator->getPheanstalks() as $name => $pheanstalk) {
            // Get information about this connection
            $this->data['pheanstalks'][$name] = [
                'name'      => $name,
                'host'      => $pheanstalk->getConnection()->getHost(),
                'port'      => $pheanstalk->getConnection()->getPort(),
                'timeout'   => $pheanstalk->getConnection()->getConnectTimeout(),
                'default'   => $defaultPheanstalk === $pheanstalk,
                'stats'     => [],
                'listening' => $pheanstalk->getConnection()->isServiceListening(),
            ];

            // If connection is not listening, there is a connection problem.
            // Skip next steps which require an established connection
            if (!$pheanstalk->getConnection()->isServiceListening()) {
                continue;
            }

            $pheanstalkStatistics = $pheanstalk->stats();

            // Get information about this connection
            $this->data['pheanstalks'][$name]['stats'] = $pheanstalkStatistics['statistics']['@attributes'];


            // Increment the number of jobs
            $this->data['jobCount'] += $pheanstalkStatistics['statistics']['@attributes']['workflow_queries'];

            // Get information about the tubes of this connection
            $tubes = $pheanstalk->listTubes();
            $this->fetchJobs($pheanstalk);
            /** @var Tube $tube */
            foreach ($tubes as $tube) {
                // Fetch next ready job and next buried job for this tube
                $stats = $pheanstalk->statsTube($tube);
                $this->data['tubes'][] = [
                    'pheanstalk' => $name,
                    'name'       => $tube->getName(),
                    'stats'      => $stats['queue']['@attributes'],
                ];
            }
        }
    }

    /**
     * @return array
     */
    public function getPheanstalks()
    {
        return $this->data['pheanstalks'];
    }

    /**
     * @return array
     */
    public function getTubes()
    {
        return $this->data['tubes'];
    }

    /**
     * @return int
     */
    public function getJobCount()
    {
        return $this->data['jobCount'];
    }

    /**
     * @return array
     */
    public function getJobs()
    {
        return $this->data['jobs'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pheanstalk';
    }

    /**
     * Get the next job ready and buried in the specified tube and connection.
     *
     * @param PheanstalkInterface $pheanstalk
     */
    private function fetchJobs(PheanstalkInterface $pheanstalk)
    {
        try {
            $nextJobReady = isset($pheanstalk->peek()[1]) ? $pheanstalk->peek()[1] : null;
            $this->data['jobs']['ready'] = [
                'id'   => isset($nextJobReady['id']) ? $nextJobReady['id'] : null,
                'data' => $nextJobReady,
            ];
        } catch (ServerException $e) {
        }
    }
}
