<?php

/**
 * Update Bundle List Command.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Command;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\SignalSlot\Repository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateBundlesListCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:update_bundles_list')
            ->setDescription('This command updates Bundle List with data gathered from Packagist.org')
            ->addOption('force', 'f', InputOption::VALUE_NONE, false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('force')) {
            $output->writeln('Force option enabled. Updating all packages.');
        }

        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $packagistServiceProvider = $this->getContainer()->get('app.packagist_service_provider');
        $contentService = $repository->getContentService();

        $query = $this->getQuery();

        $results = $repository->sudo(
            function (Repository $repository) use ($query) {
                return $repository->getSearchService()->findContent($query);
            }, $repository
        );

        foreach ($results->searchHits as $searchHit) {
            $currentPackage = $searchHit->valueObject;
            $package = $packagistServiceProvider->getPackageDetails($currentPackage->getFieldValue('bundle_id'), $input->getOption('force'));
            $output->write($currentPackage->getFieldValue('bundle_id'));

            if (($package['checksum'] !== $currentPackage->getFieldValue('checksum')->__toString()) || $input->getOption('force')) {
                $contentUpdateStruct = $this->getContentUpdateStruct($contentService, $package);

                $contentId = $searchHit->valueObject->versionInfo->contentInfo->id;
                $repository->sudo(
                    function () use ($contentService, $contentId, $contentUpdateStruct) {
                        $contentInfo = $contentService->loadContentInfo($contentId);
                        $contentDraft = $contentService->createContentDraft($contentInfo);
                        $contentDraft = $contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
                        $contentService->publishVersion($contentDraft->versionInfo);
                    }, $repository
                );

                $output->writeln(': Updated');
            }
            else {
                $output->writeln(': Already up-to-date');
            }
        }
        $output->writeln("The bundles have been successfully updated.");
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    private function getQuery() {
        $query = new Query();
        $criterion = new Query\Criterion\ParentLocationId($this->getContainer()->getParameter('bundles.location_id'));
        $query->filter = $criterion;
        return $query;
    }

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param array $package
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    private function getContentUpdateStruct(ContentService $contentService, $package) {
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = 'eng-GB';
        $contentUpdateStruct->setField('updated', (int) $package['updated']->format('U'));
        $contentUpdateStruct->setField('downloads', $package['downloads']);
        $contentUpdateStruct->setField('stars', $package['stars']);
        $contentUpdateStruct->setField('forks', $package['forks']);
        $contentUpdateStruct->setField('checksum', $package['checksum']);

        $escapedDescription = htmlspecialchars($package['description'], ENT_XML1);

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
}
