<?php

namespace Pyrowman\PheanstalkBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Pyrowman\PheanstalkBundle\DependencyInjection\LeezyPheanstalkExtension;
use Pyrowman\PheanstalkBundle\LeezyPheanstalkBundle;
use Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxy;
use Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxyInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LeezyPheanstalkExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var LeezyPheanstalkExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new LeezyPheanstalkExtension();

        $bundle = new LeezyPheanstalkBundle();
        $bundle->build($this->container); // Attach all default factories
    }

    protected function tearDown()
    {
        unset($this->container, $this->extension);
    }

    public function testInitConfiguration()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'primary' => [
                        'server'  => 'evqueue.domain.tld',
                        'port'    => 5000,
                        'timeout' => 60,
                        'default' => true,
                    ],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('pyrowman.pheanstalk.pheanstalk_locator'));
        $this->assertTrue($this->container->hasParameter('pyrowman.pheanstalk.pheanstalks'));  // Needed by ProxyCompilerPass
    }

    public function testDefaultPheanstalk()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'primary' => [
                        'server'  => 'evqueue.domain.tld',
                        'port'    => 5000,
                        'timeout' => 60,
                        'default' => true,
                    ],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('pyrowman.pheanstalk.primary'));
        $this->assertTrue($this->container->hasAlias('pyrowman.pheanstalk'));
    }

    public function testNoDefaultPheanstalk()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'primary' => [
                        'server'  => 'beanstalkd.domain.tld',
                        'port'    => 11300,
                        'timeout' => 60,
                    ],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('pyrowman.pheanstalk.primary'));
        $this->assertFalse($this->container->hasAlias('pyrowman.pheanstalk'));
    }

    /**
     * @expectedException \Pyrowman\PheanstalkBundle\Exceptions\PheanstalkException
     */
    public function testTwoDefaultPheanstalks()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'one' => [
                        'server'  => 'beanstalkd.domain.tld',
                        'default' => true,
                    ],
                    'two' => [
                        'server'  => 'beanstalkd-2.domain.tld',
                        'default' => true,
                    ],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->container->compile();
    }

    public function testMultiplePheanstalks()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'one'   => [
                        'server'  => 'beanstalkd.domain.tld',
                        'port'    => 11300,
                        'timeout' => 60,
                    ],
                    'two'   => [
                        'server' => 'beanstalkd-2.domain.tld',
                    ],
                    'three' => [
                        'server' => 'beanstalkd-3.domain.tld',
                    ],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('pyrowman.pheanstalk.one'));
        $this->assertTrue($this->container->hasDefinition('pyrowman.pheanstalk.two'));
        $this->assertTrue($this->container->hasDefinition('pyrowman.pheanstalk.three'));

        # @see https://github.com/armetiz/pyrowmanPheanstalkBundle/issues/61
        $this->assertNotSame($this->container->getDefinition('pyrowman.pheanstalk.one'), $this->container->getDefinition('pyrowman.pheanstalk.two'));
    }

    public function testPheanstalkLocator()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'primary' => [
                        'server'  => 'beanstalkd.domain.tld',
                        'port'    => 11300,
                        'timeout' => 60,
                        'default' => true,
                    ],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('pyrowman.pheanstalk.pheanstalk_locator'));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function testPheanstalkProxyCustomTypeNotDefined()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'primary' => [
                        'server'  => 'beanstalkd.domain.tld',
                        'port'    => 11300,
                        'timeout' => 60,
                        'proxy'   => 'acme.pheanstalk.pheanstalk_proxy',
                    ],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->container->compile();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testPheanstalkReservedName()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'proxy' => [
                        'server'  => 'beanstalkd.domain.tld',
                        'port'    => 11300,
                        'timeout' => 60,
                        'proxy'   => 'acme.pheanstalk.pheanstalk_proxy',
                    ],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->container->compile();
    }

    public function testPheanstalkProxyCustomType()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'primary' => [
                        'server'  => 'evqueue.domain.tld',
                        'port'    => 5000,
                        'timeout' => 60,
                        'proxy'   => 'acme.pheanstalk.pheanstalk_proxy',
                    ],
                ],
            ],
        ];

        $this->container->setDefinition('acme.pheanstalk.pheanstalk_proxy', new Definition(PheanstalkProxy::class));

        $this->extension->load($config, $this->container);
        $this->container->compile();
        $this->assertNotNull($this->container->get('pyrowman.pheanstalk.primary'));
    }

    public function testLoggerConfiguration()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'primary' => [
                        'server'  => 'beanstalkd.domain.tld',
                        'port'    => 11300,
                        'timeout' => 60,
                        'default' => true,
                    ],
                ],
            ],
        ];

        $this->container->setDefinition('logger', new Definition(NullLogger::class));

        $this->extension->load($config, $this->container);
        $this->container->compile();

        $this->assertTrue($this->container->hasDefinition('pyrowman.pheanstalk.listener.log'));
        $listener = $this->container->getDefinition('pyrowman.pheanstalk.listener.log');

        $this->assertTrue($listener->hasMethodCall('setLogger'));
        $this->assertTrue($listener->hasTag('monolog.logger'));

        $tag = $listener->getTag('monolog.logger');
        $this->assertEquals('pheanstalk', $tag[0]['channel']);
    }

    public function testPheanstalkProfilerDisabled()
    {
        $config = [
            'pyrowman_pheanstalk' => [
                'pheanstalks' => [
                    'primary' => [
                        'server'  => 'evqueue.domain.tld',
                        'port'    => 5000,
                        'timeout' => 60,
                        'proxy'   => 'acme.pheanstalk.pheanstalk_proxy',
                    ],
                ],
                'profiler' => [
                    "enabled" => false
                ]
            ],
        ];

        $this->container->setDefinition('acme.pheanstalk.pheanstalk_proxy', new Definition(PheanstalkProxy::class));

        $this->extension->load($config, $this->container);
        $this->container->compile();
        $this->assertFalse($this->container->hasDefinition('pyrowman.pheanstalk.data_collector'));
    }
}
