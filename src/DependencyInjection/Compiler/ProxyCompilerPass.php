<?php

/*
 * (c) 2013 Wozbe
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pyrowman\PheanstalkBundle\DependencyInjection\Compiler;

use Pyrowman\PheanstalkBundle\Exceptions\PheanstalkException;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Pyrowman\PheanstalkBundle\PheanstalkLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Description of ProxyCompilerPass.
 *
 * @author Thomas Tourlourat <thomas@tourlourat.com>
 */
class ProxyCompilerPass implements CompilerPassInterface
{
    protected $defaultPheanstalkName = null;

    protected function reservedName()
    {
        return [
            'pheanstalks',
            'pheanstalk_locator',
            'proxy',
            'data_collector',
            'listener',
            'event',
        ];
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws PheanstalkException
     */
    public function process(ContainerBuilder $container)
    {
        $pheanstalks = $container->getParameter('pyrowman.pheanstalk.pheanstalks');

        // For each connection in the configuration file
        foreach ($pheanstalks as $name => $pheanstalk) {
            $this->addServer($name, $pheanstalk, $container);
        }
    }

    /**
     * @param string           $name
     * @param array            $pheanstalk
     * @param ContainerBuilder $container
     *
     * @throws PheanstalkException
     */
    protected function addServer($name, array $pheanstalk, ContainerBuilder $container)
    {
        $pheanstalkLocatorDef = $container->getDefinition('pyrowman.pheanstalk.pheanstalk_locator');
        if (in_array($name, $this->reservedName())) {
            throw new \RuntimeException('Reserved pheanstalk name: ' . $name);
        }

        $pheanstalkConfig = [
            $pheanstalk['server'],
            $pheanstalk['user'] ?? null,
            $pheanstalk['password'] ?? null,
            $pheanstalk['port'],
            $pheanstalk['timeout']
        ];
        $isDefault = $pheanstalk['default'];

        # @see https://github.com/armetiz/pyrowmanPheanstalkBundle/issues/61
        $pheanstalkDef = clone $container->getDefinition($pheanstalk['proxy']);

        $pheanstalkDef->addMethodCall('setPheanstalk', [new Definition(Pheanstalk::class, $pheanstalkConfig)]);
        $pheanstalkDef->addMethodCall('setName', [$name]);
        $pheanstalkDef->setPublic(true);

        $container->setDefinition('pyrowman.pheanstalk.' . $name, $pheanstalkDef);

        // Register the connection in the connection locator
        $pheanstalkLocatorDef->addMethodCall('addPheanstalk', [
            $name,
            $container->getDefinition('pyrowman.pheanstalk.' . $name),
            $isDefault,
        ]);

        if ($isDefault) {
            $this->autowireDefaultConfig($name, $container);
        }
    }

    /**
     * @param string $name
     * @param ContainerBuilder $container
     *
     * @throws PheanstalkException
     */
    protected function autowireDefaultConfig($name, ContainerBuilder $container)
    {
        if (null !== $this->defaultPheanstalkName) {
            throw new PheanstalkException(sprintf('Default pheanstalk already defined. "%s" & "%s"', $this->defaultPheanstalkName, $name));
        }

        $this->defaultPheanstalkName = $name;
        $legacyAlias = $container->setAlias('pyrowman.pheanstalk', 'pyrowman.pheanstalk.' . $name);
        $legacyAlias->setPublic(true);

        $autoWiringAlias = $container->setAlias(PheanstalkInterface::class, 'pyrowman.pheanstalk');
        $autoWiringAlias->setPublic(true);
    }
}
