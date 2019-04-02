<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\PackageRepositoryProviderCompilerPass;
use AppBundle\DependencyInjection\Compiler\RegisterTagCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AppBundle.
 */
class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterTagCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10000);
        $container->addCompilerPass(new PackageRepositoryProviderCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
