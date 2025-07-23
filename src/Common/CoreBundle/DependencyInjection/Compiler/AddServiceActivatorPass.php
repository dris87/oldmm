<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) https://ujallas.hu
 *
 * Developed by: Ferencz Dávid Tamás <fdt0712@gmail.com>
 * Contributed: Sipos Zoltán <sipiszoty@gmail.com>, Pintér Szilárd <leaderlala00@gmail.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Common\CoreBundle\DependencyInjection\Compiler;

use Common\CoreBundle\Manager\Firm\FirmBalanceManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddServiceActivatorPass.
 */
class AddServiceActivatorPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $manager_definition = $container->getDefinition(FirmBalanceManager::class);

        foreach ($container->findTaggedServiceIds('balance.activator') as $id => $tags) {
            $manager_definition->addMethodCall('addServiceActivator', [
                new Reference($id),
            ]);
        }
    }
}
