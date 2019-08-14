<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\DependencyInjection\Compiler;

use AppBundle\Service\PackageRepository\PackageRepositoryStrategy;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PackageRepositoryCompilerPass implements CompilerPassInterface
{
    const STRATEGY_TAG_NAME = 'package.repository.strategy';

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $packageRepositoryStrategy = $container->findDefinition(PackageRepositoryStrategy::class);
        $services = array_keys($container->findTaggedServiceIds(self::STRATEGY_TAG_NAME));

        foreach ($services as $service) {
            $packageRepositoryStrategy->addMethodCall(
                'addPackageRepositoryService',
                [new Reference($service)]
            );
        }
    }
}
