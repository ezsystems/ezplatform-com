<?php

/**
 * Update Package List Command.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Command;

use AppBundle\Helper\RichTextHelper;
use AppBundle\Service\Cache\CacheServiceInterface;
use AppBundle\Service\Package\PackageServiceInterface;
use AppBundle\ValueObject\Package;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;
use eZ\Publish\API\Repository\PermissionResolver as PermissionResolverInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\ValueObject;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePackageListCommand extends AbstractUpdatePackageCommand
{
    /** @var \AppBundle\Helper\RichTextHelper */
    private $richTextHelper;

    /**
     * @param \AppBundle\Helper\RichTextHelper $richTextHelper
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \AppBundle\Service\Package\PackageServiceInterface $packageService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \AppBundle\Service\Cache\CacheServiceInterface $cacheService
     * @param int $adminId
     * @param int $packagesLocationId
     */
    public function __construct(
        RichTextHelper $richTextHelper,
        PermissionResolverInterface $permissionResolver,
        UserServiceInterface $userService,
        PackageServiceInterface $packageService,
        ContentServiceInterface $contentService,
        SearchServiceInterface $searchService,
        CacheServiceInterface $cacheService,
        int $adminId,
        int $packagesLocationId
    ) {
        $this->richTextHelper = $richTextHelper;

        parent::__construct(
            $permissionResolver,
            $userService,
            $packageService,
            $contentService,
            $searchService,
            $cacheService,
            $adminId,
            $packagesLocationId
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('app:update_package_list')
            ->setDescription('This command updates Package List with data gathered from Packagist.org')
            ->addOption('force', 'f', InputOption::VALUE_NONE, false)
            ->addOption('details', 'd', InputOption::VALUE_NONE, false);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('force')) {
            $output->writeln('Force option enabled. Updating all packages.');
        }

        $this->setPermissionResolver();

        $results = $this->getPackages();

        $packagesToInvalidate = [];

        foreach ($results as $searchHit) {
            $currentPackage = $searchHit->valueObject;
            $package = $this->packageService->getPackage($currentPackage->getFieldValue('package_id')->text, $input->getOption('force'));

            if ($output->isVerbose()) {
                $output->writeln('Package repository: ' . $package->repository);
            }

            $output->write('<question>' . $currentPackage->getFieldValue('package_id') . '</question>');

            if (($package->checksum !== $currentPackage->getFieldValue('checksum')->__toString()) || $input->getOption('force')) {
                if (!empty($this->getDiff($currentPackage, $package)) && $input->getOption('details')) {
                    $output->writeln(': <info>Updated</info>');
                    $table = new Table($output);
                    $table->setHeaders(['Field', 'Old value', 'New value']);
                    $table->setRows($this->getDiff($currentPackage, $package));
                    $table->render();
                } else {
                    $output->writeln(': <info>Updated.</info>');
                }

                $contentUpdateStruct = $this->getContentUpdateStruct($package);
                $contentId = $searchHit->valueObject->versionInfo->contentInfo->id;

                if ($this->updatePackage($contentId, $contentUpdateStruct)) {
                    $packagesToInvalidate = $this->addPackageToInvalidateTag($packagesToInvalidate, $currentPackage);
                }
            } else {
                $output->writeln(': <comment>Already up-to-date</comment>');
            }
        }

        $this->cacheService->invalidateTags($packagesToInvalidate);

        $output->writeln('<info>The packages have been successfully updated.</info>');
    }

    /**
     * @param int $contentId
     * @param \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function updatePackage(int $contentId, ContentUpdateStruct $contentUpdateStruct): Content
    {
        $contentInfo = $this->contentService->loadContentInfo($contentId);
        $contentDraft = $this->contentService->createContentDraft($contentInfo);
        $contentDraft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);

        return $this->contentService->publishVersion($contentDraft->versionInfo);
    }

    /**
     * @param \AppBundle\ValueObject\Package $package
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    private function getContentUpdateStruct(Package $package): ContentUpdateStruct
    {
        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = 'eng-GB';
        $contentUpdateStruct->setField('checksum', $package->checksum);
        $contentUpdateStruct->setField('readme', $package->readme);
        $contentUpdateStruct->setField('description', $this->richTextHelper->getXmlString($package->description));

        return $contentUpdateStruct;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ValueObject $current
     * @param \AppBundle\ValueObject\Package $package
     *
     * @return array
     */
    private function getDiff(ValueObject $current, Package $package): array
    {
        $diff = [];
        foreach (get_object_vars($package) as $key => $value) {
            if ($key == 'description') {
                continue;
            }
            if ($key == 'updated') {
                if ($current->getFieldValue('updated')->date != $value) {
                    $diff[] = [
                        'name' => 'updated',
                        'old' => $current->getFieldValue('updated')->date != null ? $current->getFieldValue('updated')->date->format(\DateTime::ISO8601) : '',
                        'new' => $value->format(\DateTime::ISO8601),
                    ];
                }
                continue;
            }
            try {
                if ($current->getFieldValue($key) != null && $current->getFieldValue($key)->__toString() != $value) {
                    $diff[] = [
                        'name' => $key,
                        'old' => $current->getFieldValue($key)->__toString(),
                        'new' => $value,
                    ];
                }
            } catch (PropertyNotFoundException $notFoundException) {
                continue;
            }
        }

        return $diff;
    }
}
