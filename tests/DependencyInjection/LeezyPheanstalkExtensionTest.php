<?php

namespace Pyrowman\PheanstalkBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Pyrowman\PheanstalkBundle\DependencyInjection\PheanstalkExtension;
use Pyrowman\PheanstalkBundle\PheanstalkBundle;
use Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxy;
use Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxyInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class PheanstalkExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var PheanstalkExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new PheanstalkExtension();

        $bundle = new PheanstalkBundle();
        $bundle->build($this->container); // Attach all default factories
    }

    protected function tearDown()
    {
        unset($this->container, $this->extension);
    }

    public function testInitConfiguration()
    {
        $config = [
            'pheanstalk' => [
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

        $this->assertTrue($this->container->hasDefinition('pheanstalk.pheanstalk_locator'));
        $this->assertTrue($this->container->hasParameter('pheanstalk.pheanstalks'));  // Needed by ProxyCompilerPass
    }

    public function testDefaultPheanstalk()
    {
        $config = [
            'pheanstalk' => [
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

        $this->assertTrue($this->container->hasDefinition('pheanstalk.primary'));
        $this->assertTrue($this->container->hasAlias('pheanstalk'));
    }

    public function testNoDefaultPheanstalk()
    {
        $config = [
            'pheanstalk' => [
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

        $this->assertTrue($this->container->hasDefinition('pheanstalk.primary'));
        $this->assertFalse($this->container->hasAlias('pheanstalk'));
    }

    /**
     * @expectedException \Pyrowman\PheanstalkBundle\Exceptions\PheanstalkException
     */
    public function testTwoDefaultPheanstalks()
    {
        $config = [
            'pheanstalk' => [
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
            'pheanstalk' => [
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

        $this->assertTrue($this->container->hasDefinition('pheanstalk.one'));
        $this->assertTrue($this->container->hasDefinition('pheanstalk.two'));
        $this->assertTrue($this->container->hasDefinition('pheanstalk.three'));

        # @see https://github.com/armetiz/pyrowmanPheanstalkBundle/issues/61
        $this->assertNotSame($this->container->getDefinition('pheanstalk.one'), $this->container->getDefinition('pheanstalk.two'));
    }

    public function testPheanstalkLocator()
    {
        $config = [
            'pheanstalk' => [
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

        $this->assertTrue($this->container->hasDefinition('pheanstalk.pheanstalk_locator'));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function testPheanstalkProxyCustomTypeNotDefined()
    {
        $config = [
            'pheanstalk' => [
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
            'pheanstalk' => [
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
            'pheanstalk' => [
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
        $this->assertNotNull($this->container->get('pheanstalk.primary'));
    }

    public function testLoggerConfiguration()
    {
        $config = [
            'pheanstalk' => [
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

        $this->assertTrue($this->container->hasDefinition('pheanstalk.listener.log'));
        $listener = $this->container->getDefinition('pheanstalk.listener.log');

        $this->assertTrue($listener->hasMethodCall('setLogger'));
        $this->assertTrue($listener->hasTag('monolog.logger'));

        $tag = $listener->getTag('monolog.logger');
        $this->assertEquals('pheanstalk', $tag[0]['channel']);
    }

    public function testPheanstalkProfilerDisabled()
    {
        $config = [
            'pheanstalk' => [
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
        $this->assertFalse($this->container->hasDefinition('pheanstalk.data_collector'));
    }

}
