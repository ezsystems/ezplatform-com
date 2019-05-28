<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Command;

use AppBundle\ValueObject\Package;
use AppBundle\ValueObject\PackageMetadata;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePackageMetadataCommand extends AbstractUpdatePackageCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('app:update_package_metadata')
            ->setDescription('This command updates Package Metadata with data gathered from Packagist.org');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->setPermissionResolver();
        $packages = $this->getPackages();
        $invalidateTags = [];
        $changedPackages = [];

        foreach ($packages as $searchHit) {
            $package = $searchHit->valueObject;
            $packageId = $package->getFieldValue('package_id')->text;
            $escapedPackageId = $this->packageService->removeReservedCharactersFromPackageName($packageId);
            $item = $this->cacheService->getItem($escapedPackageId);
            /** @var Package $package */
            $cachePackage = $item->get();

            if ($cachePackage) {
                $output->write('<question>' . $packageId . '</question>');

                $apiPackage = $this->packageService->getPackageFromPackagist($cachePackage->packageId);
                if ($this->hasChanged($cachePackage->packageMetadata, $apiPackage->packageMetadata)) {
                    $output->writeln(': <info>Updated.</info>');
                    $cachePackage->packageMetadata = $apiPackage->packageMetadata;
                    $invalidateTags = array_merge(
                        $invalidateTags,
                        $this->addPackageToInvalidateTag($invalidateTags, $package)
                    );
                    $changedPackages[] = $this->setCacheItem($item, $cachePackage);
                } else {
                    $output->writeln(': <comment>Already up-to-date</comment>');
                }
            }
        }

        if (!empty($invalidateTags)) {
            $this->cacheService->invalidateTags($invalidateTags);
        }

        if (!empty($changedPackages)) {
            $this->cacheService->saveCacheItems($changedPackages);
        }
    }

    /**
     * @param \AppBundle\ValueObject\Package $packageMetadata
     * @param \AppBundle\ValueObject\Package $apiPackageMetadata
     *
     * @return bool
     */
    private function hasChanged(PackageMetadata $packageMetadata, PackageMetadata $apiPackageMetadata): bool
    {
        return md5(serialize($packageMetadata)) !== md5(serialize($apiPackageMetadata));
    }

    /**
     * @param \Psr\Cache\CacheItemInterface $cacheItem
     * @param Package $cachePackage
     *
     * @return \Psr\Cache\CacheItemInterface
     */
    private function setCacheItem(CacheItemInterface $cacheItem, Package $cachePackage): CacheItemInterface
    {
        $cacheItem->expiresAfter((int) $this->cacheService->getCacheExpirationTime());
        $cacheItem->set($cachePackage);

        return $cacheItem;
    }
}
