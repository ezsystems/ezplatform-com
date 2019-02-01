<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Event\Workflow;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Repository;
use EzSystems\EzPlatformWorkflow\Event\StageChangeEvent;
use EzSystems\EzPlatformWorkflow\Event\WorkflowEvents;
use EzSystems\EzPlatformWorkflow\Registry\WorkflowDefinitionMetadataRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Transition;

class PublishOnLastStageSubscriber implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \EzSystems\EzPlatformWorkflow\Registry\WorkflowDefinitionMetadataRegistry */
    private $workflowMetadataRegistry;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \EzSystems\FlexWorkflow\API\Repository\RepositoryInterface */
    private $repository;

    private $publishOnLastStageWorkflows;

    /**
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \EzSystems\EzPlatformWorkflow\Registry\WorkflowDefinitionMetadataRegistry $workflowMetadataRegistry
     * @param array $publishOnLastStageWorkflows
     */
    public function __construct(
        PermissionResolver $permissionResolver,
        Repository $repository,
        ContentService $contentService,
        WorkflowDefinitionMetadataRegistry $workflowMetadataRegistry,
        array $publishOnLastStageWorkflows
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->workflowMetadataRegistry = $workflowMetadataRegistry;
        $this->contentService = $contentService;
        $this->repository = $repository;
        $this->publishOnLastStageWorkflows = $publishOnLastStageWorkflows;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkflowEvents::WORKFLOW_STAGE_CHANGE => ['onStageChange', 30],
        ];
    }

    public function onStageChange(StageChangeEvent $event): void
    {
        $workflowName = $event->getWorkflowMetadata()->name;

        if (!\in_array($workflowName, $this->publishOnLastStageWorkflows, true)
            || !$this->workflowMetadataRegistry->hasWorkflowMetadata($workflowName)
        ) {
            return;
        }

        $workflowDefinitionMetadata = $this->workflowMetadataRegistry->getWorkflowMetadata($workflowName);
        $workflowDefinition = $event->getWorkflowMetadata()->workflow->getDefinition();

        $transitionName = $event->getTransitionMetadata()->name;
        $workflowTos = $this->getWorkflowTransitionTos($transitionName, $workflowDefinition);

        foreach ($workflowTos as $stageName) {
            $isLastStage = $workflowDefinitionMetadata->getStageMetadata($stageName)->isLastStage();

            if ($isLastStage) {
                $this->permissionResolver->sudo(function () use ($event) {
                    $this->contentService->publishVersion($event->getWorkflowMetadata()->versionInfo);
                }, $this->repository);
            }
        }
    }

    private function getWorkflowTransitionTos(string $transitionName, Definition $workflowDefinition)
    {
        $workflowTransitions = array_filter($workflowDefinition->getTransitions(), function (Transition $item) use ($transitionName) {
            return $transitionName === $item->getName();
        });

        $tos = [];

        return array_reduce($workflowTransitions, function ($tos, Transition $item) {
            return array_merge($tos, $item->getTos());
        }, $tos);
    }
}
