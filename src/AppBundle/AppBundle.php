<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\PackageRepositoryCompilerPass;
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
        $container->addCompilerPass(new PackageRepositoryCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
