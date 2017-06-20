<?php

namespace AppBundle\Controller;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TagController
{
    /**
     * @var \Symfony\Bundle\TwigBundle\TwigEngine
     */
    private $templating;

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var int
     */
    private $relatedContentLimit;

    /**
     * TagController constructor.
     * @param EngineInterface $templating
     * @param TagsService $tagsService
     * @param int $relatedContentLimit
     */
    public function __construct(
        EngineInterface $templating,
        TagsService $tagsService,
        $relatedContentLimit
    )
    {
        $this->templating = $templating;
        $this->tagsService = $tagsService;
        $this->relatedContentLimit = $relatedContentLimit;
    }

    /**
     * @param int $tagId
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getTagRelatedContent($tagId, $page)
    {
        $offset = $page * $this->relatedContentLimit - $this->relatedContentLimit;
        try {
            $tag = $this->tagsService->loadTag($tagId);
            $relatedContent = $this->tagsService->getRelatedContent($tag, $offset, $this->relatedContentLimit);
            $relatedContentCount = $this->tagsService->getRelatedContentCount($tag);
            $renderedContent = $this->templating->render('parts/tag/list.html.twig', [
                'items' => $relatedContent,
                'viewType' => 'line'
            ]);

            $showMoreButton = $relatedContentCount > ($page * $this->relatedContentLimit) ? true : false;

            return new JsonResponse([
                'html' => $renderedContent,
                'showLoadMoreButton' => $showMoreButton,
            ]);
        }
        catch (NotFoundException $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 404);
        }
        catch (UnauthorizedException $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 403);
        }
    }
}
