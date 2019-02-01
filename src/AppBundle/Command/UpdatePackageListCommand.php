<?php

/**
 * Update Package List Command.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Command;

use AppBundle\Service\Cache\CacheServiceInterface;
use AppBundle\Service\DOM\DOMServiceInterface;
use AppBundle\Service\GitHub\GitHubServiceProvider;
use AppBundle\Service\Package\PackageServiceInterface;
use AppBundle\Service\PackageRepository\PackageRepositoryServiceInterface;
use AppBundle\ValueObject\Package;
use AppBundle\ValueObject\RepositoryMetadata;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\ValueObject;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class UpdatePackageListCommand extends ContainerAwareCommand
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \AppBundle\Service\Cache\CacheServiceInterface
     */
    private $cacheService;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \AppBundle\Service\Package\PackageServiceInterface
     */
    private $packageService;

    /**
     * @var \AppBundle\Service\PackageRepository\PackageRepositoryServiceInterface
     */
    private $packageRepositoryService;

    /**
     * @var \AppBundle\Service\DOM\DOMServiceInterface
     */
    private $domService;

    public function __construct(
        Repository $repository,
        CacheServiceInterface $cacheService,
        PackageServiceInterface $packageService,
        PackageRepositoryServiceInterface $packageRepositoryService,
        DOMServiceInterface $domService
    ) {
        $this->repository = $repository;
        $this->contentService = $this->repository->getContentService();
        $this->cacheService = $cacheService;
        $this->packageService = $packageService;
        $this->packageRepositoryService = $packageRepositoryService;
        $this->domService = $domService;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:update_package_list')
            ->setDescription('This command updates Package List with data gathered from Packagist.org')
            ->addOption('force', 'f', InputOption::VALUE_NONE, false)
            ->addOption('details', 'd', InputOption::VALUE_NONE, false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('force')) {
            $output->writeln('Force option enabled. Updating all packages.');
        }

        $query = $this->getQuery();

        $results = $this->repository->sudo(
            function (Repository $repository) use ($query) {
                return $repository->getSearchService()->findContent($query);
            }, $this->repository
        );

        $packagesToInvalidate = [];

        foreach ($results->searchHits as $searchHit) {
            $currentPackage = $searchHit->valueObject;

            $package = $this->packageService->getPackage($currentPackage->getFieldValue('package_id'), $input->getOption('force'));
            $output->write('<question>'.$currentPackage->getFieldValue('package_id').'</question>');

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

                $this->repository->sudo(
                    function () use ($contentId, $contentUpdateStruct) {
                        $contentInfo = $this->contentService->loadContentInfo($contentId);
                        $contentDraft = $this->contentService->createContentDraft($contentInfo);
                        $contentDraft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
                        $this->contentService->publishVersion($contentDraft->versionInfo);
                    }, $this->repository
                );

                $packagesToInvalidate[] = 'content-' . $contentId;
                $packagesToInvalidate[] = 'location-' . $currentPackage->versionInfo->contentInfo->mainLocationId;
            } else {
                $output->writeln(': <comment>Already up-to-date</comment>');
            }
        }

        $this->cacheService->invalidateTags($packagesToInvalidate);

        $output->writeln('<info>The packages have been successfully updated.</info>');
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    private function getQuery()
    {
        $query = new Query();
        $criterion = new Query\Criterion\LogicalAnd([
            new Query\Criterion\ParentLocationId($this->getContainer()->getParameter('packages.location_id')),
            new Query\Criterion\ContentTypeIdentifier('package')
        ]);

        $query->filter = $criterion;
        $query->limit = 1000;

        return $query;
    }

    /**
     * @param \AppBundle\ValueObject\Package $package
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    private function getContentUpdateStruct(Package $package)
    {
        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = 'eng-GB';
        $contentUpdateStruct->setField('updated', (int)$package->updateDate->format('U'));
        $contentUpdateStruct->setField('downloads', $package->downloads);
        $contentUpdateStruct->setField('stars', $package->stars);
        $contentUpdateStruct->setField('forks', $package->forks);
        $contentUpdateStruct->setField('checksum', $package->checksum);

        $readme = $this->packageRepositoryService->getReadme(new RepositoryMetadata($package->repository));

        if ($readme) {
            $crawler = new Crawler($readme);
            $this->domService->removeElementsFromDOM($crawler, ['.anchor', '[data-canonical-src]']);
            $this->domService->setAbsoluteURL($crawler, ['repository' => $package->repository, 'link' => GitHubServiceProvider::GITHUB_URL_PARTS]);
            $contentUpdateStruct->setField('readme', $crawler->html());
        }

        $escapedDescription = htmlspecialchars($package->description, ENT_XML1);

        $xmlText = <<< EOX
<?xml version='1.0' encoding='utf-8'?>
<section 
    xmlns="http://docbook.org/ns/docbook" 
    xmlns:xlink="http://www.w3.org/1999/xlink" 
    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" 
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" 
    version="5.0-variant ezpublish-1.0">
<para>{$escapedDescription}</para>
</section>
EOX;
        $contentUpdateStruct->setField('description', $xmlText);

        return $contentUpdateStruct;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ValueObject $current
     * @param \AppBundle\ValueObject\Package $package
     *
     * @return array
     */
    private function getDiff(ValueObject $current, Package $package)
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
