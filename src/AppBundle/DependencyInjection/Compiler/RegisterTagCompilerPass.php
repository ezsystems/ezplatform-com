<?php

/**
 * RegisterTagCompilerPass.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\DependencyInjection\Compiler;

use AppBundle\Service\PackageRepository\PackageRepositoryServiceProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class RegisterPackageRepositoryProviderTagCompilerPass.
 */
class RegisterTagCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(PackageRepositoryServiceProviderInterface::class)
            ->addTag(PackageRepositoryProviderCompilerPass::STRATEGY_TAG_NAME);
    }
}
