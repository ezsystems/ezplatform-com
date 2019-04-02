<?php

/**
 * PackageRepositoryProviderCompilerPass.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\DependencyInjection\Compiler;

use AppBundle\Service\PackageRepository\PackageRepositoryProviderStrategy;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PackageRepositoryProviderCompilerPass.
 */
class PackageRepositoryProviderCompilerPass implements CompilerPassInterface
{
    const STRATEGY_TAG_NAME = 'package.repository.provider.strategy';

    public function process(ContainerBuilder $container)
    {
        $packageRepositoryProvider = $container->findDefinition(PackageRepositoryProviderStrategy::class);

        $providers = array_keys($container->findTaggedServiceIds(self::STRATEGY_TAG_NAME));

        foreach ($providers as $provider) {
            $packageRepositoryProvider->addMethodCall(
                'addPackageRepositoryServiceProvider',
                [new Reference($provider)]
            );
        }
    }
}
