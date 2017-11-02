<?php

namespace AppBundle\Controller;

use AppBundle\Form\OrderType;
use AppBundle\Form\SearchType;
use AppBundle\QueryType\BundlesQueryType;
use AppBundle\Service\Packagist\PackagistServiceProviderInterface;
use eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class BundleController
{
    /**
     * @var \Symfony\Bundle\TwigBundle\TwigEngine
     */
    private $templating;

    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    /**
     * @var \AppBundle\QueryType\BundlesQueryType
     */
    private $bundlesQueryType;

    /**
     * @var \AppBundle\Service\Packagist\PackagistServiceProviderInterface;
     */
    private $packagistServiceProvider;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    private $formFactory;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter
     */
    private $router;

    /**
     * @var int
     */
    private $bundlesListLocationId;

    /**
     * @var int
     */
    private $bundlesListCardsLimit;

    /**
     * BundleController constructor.
     * @param EngineInterface $templating
     * @param SearchService $searchService
     * @param BundlesQueryType $bundlesQueryType
     * @param PackagistServiceProviderInterface $packagistServiceProvider
     * @param FormFactory $formFactory
     * @param DefaultRouter $router
     * @param int $bundlesListLocationId
     * @param int $bundlesListCardsLimit
     */
    public function __construct(
        EngineInterface $templating,
        SearchService $searchService,
        BundlesQueryType $bundlesQueryType,
        PackagistServiceProviderInterface $packagistServiceProvider,
        FormFactory $formFactory,
        DefaultRouter $router,
        $bundlesListLocationId,
        $bundlesListCardsLimit
    ) {
        $this->templating = $templating;
        $this->searchService = $searchService;
        $this->bundlesQueryType = $bundlesQueryType;
        $this->packagistServiceProvider = $packagistServiceProvider;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->bundlesListLocationId = $bundlesListLocationId;
        $this->bundlesListCardsLimit = $bundlesListCardsLimit;
    }

    /**
     * Renders full view `bundle_list` with first page elements.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showBundlesListAction(Request $request)
    {
        $sortingForm = $this->formFactory->create(OrderType::class);
        $searchForm = $this->formFactory->create(SearchType::class);

        $order = 'default';

        $sortingForm->handleRequest($request);
        if ($sortingForm->isSubmitted() && $sortingForm->isValid()) {
            $order = $sortingForm->get('order')->getData();
        }

        $searchResults = $this->getLocations(0, $order);
        $query = new Query();
        $criterion = new Query\Criterion\LocationId($this->bundlesListLocationId);
        $query->filter = $criterion;
        $contentSearchResult = $this->searchService->findContent($query);
        $content = $contentSearchResult->searchHits[0]->valueObject;
        $bundles = $this->getList($searchResults);

        return $this->templating->renderResponse('@ezdesign/full/bundle_list.html.twig', [
            'items' => $bundles,
            'content' => $content,
            'viewType' => 'full',
            'extraParams' => [
                'totalCount' => $searchResults->totalCount,
                'page' => 1,
            ],
            'sortingForm' => $sortingForm->createView(),
            'searchForm' => $searchForm->createView()
        ]);
    }

    /**
     * Renders `bundle_list` partial `list` view for given $page.
     *
     * @param int $page
     * @param string|null $order
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getBundlesListAction($page, $order = null)
    {
        $offset = $page * $this->bundlesListCardsLimit - $this->bundlesListCardsLimit;
        $searchResults = $searchResults = $this->getLocations($offset, $order);
        $bundles = $this->getList($searchResults);

        $renderedContent = $this->templating->render('@ezdesign/parts/bundle_list/list.html.twig', [
            'items' => $bundles,
            'viewType' => 'line',
            'extraParams' => [
                'totalCount' => $searchResults->totalCount,
                'page' => $page,
            ],
        ]);

        $showMoreButton = $searchResults->totalCount > ($offset + count($searchResults->searchHits)) ? true : false;

        return new JsonResponse([
            'html' => $renderedContent,
            'showLoadMoreButton' => $showMoreButton,
        ]);
    }

    /**
     * Validates search query.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function searchBundlesAction(Request $request)
    {
        $searchText = '';

        $searchForm = $this->formFactory->create(SearchType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchText = $searchForm->get('search')->getData();
        }

        return new RedirectResponse($this->router->generate('_ezplatform_bundles_search_order_list', [
            'searchText' => $searchText,
            'order' => 'default'
        ]));
    }

    /**
     * Renders full view `bundle_list` for the first page with bundles matched query
     *
     * @param string $searchText
     * @param string|null $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getOrderedSearchBundlesListAction($searchText, $order = null)
    {
        $sortingForm = $this->formFactory->create(OrderType::class, [
            'order' => $order
        ]);
        $searchForm = $this->formFactory->create(SearchType::class, [
            'search' => $searchText
        ]);

        $searchResults = $searchResults = $this->getLocations(0, $order, $searchText);
        $query = new Query();
        $criterion = new Query\Criterion\LocationId($this->bundlesListLocationId);
        $query->filter = $criterion;
        $contentSearchResult = $this->searchService->findContent($query);
        $content = $contentSearchResult->searchHits[0]->valueObject;
        $bundles = $this->getList($searchResults);

        return $this->templating->renderResponse('@ezdesign/full/bundle_list.html.twig', [
            'items' => $bundles,
            'content' => $content,
            'viewType' => 'full',
            'extraParams' => [
                'totalCount' => $searchResults->totalCount,
                'page' => 1,
            ],
            'search' => $searchText,
            'sortingForm' => $sortingForm->createView(),
            'searchForm' => $searchForm->createView()
        ]);
    }

    /**
     * Renders `bundle_list` partial `list` view for given $searchText, $page and $order.
     *
     * @param string $searchText
     * @param int $page
     * @param string|null $order
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOrderedSearchBundlesAction($searchText, $page = 1, $order = null)
    {
        $offset = $page * $this->bundlesListCardsLimit - $this->bundlesListCardsLimit;
        $searchResults = $searchResults = $this->getLocations($offset, $order, $searchText);
        $bundles = $this->getList($searchResults);

        $renderedContent = $this->templating->render('@ezdesign/parts/bundle_list/list.html.twig', [
            'items' => $bundles,
            'viewType' => 'line',
            'extraParams' => [
                'totalCount' => $searchResults->totalCount,
                'page' => $page,
            ],
            'search' => $searchText
        ]);

        $showMoreButton = $searchResults->totalCount > ($offset + count($searchResults->searchHits)) ? true : false;

        return new JsonResponse([
            'html' => $renderedContent,
            'showLoadMoreButton' => $showMoreButton,
        ]);
    }

    /**
     * Prepares and runs search query.
     *
     * @param int $offset
     * @param string|null $order
     * @param string $searchText
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    private function getLocations($offset = 0, $order = null, $searchText = '')
    {
        $query = $this->bundlesQueryType->getQuery([
            'parent_location_id' => $this->bundlesListLocationId,
            'limit' => $this->bundlesListCardsLimit,
            'offset' => $offset,
            'order' => $order,
            'search' => $searchText
        ]);

        return $this->searchService->findLocations($query);
    }

    /**
     * Returns list of bundles with package details for given $searchResult set.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResults
     * @return array
     */
    private function getList(SearchResult $searchResults)
    {
        $bundles = [];
        foreach ($searchResults->searchHits as $searchHit) {
            $bundles[] = [
                'bundle' => $searchHit,
                'packageDetails' => $this->packagistServiceProvider->getPackageDetails($searchHit->valueObject->contentInfo->name)
            ];
        }

        return $bundles;
    }
}
