<?php

namespace AppBundle\Service\Packagist;

interface PackagistServiceProviderInterface
{
    public function getPackageDetails($packageName);
}
