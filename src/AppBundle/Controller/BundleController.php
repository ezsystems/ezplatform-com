<?php

namespace AppBundle\Controller;

use AppBundle\Form\BundleOrderType;
use AppBundle\Form\BundleSearchType;
use AppBundle\QueryType\BundlesQueryType;
use AppBundle\Service\Packagist\PackagistServiceProviderInterface;
use eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter;
use eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
     * @var \eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter
     */
    private $aliasRouter;

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
     * @param UrlAliasRouter $aliasRouter
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
        UrlAliasRouter $aliasRouter,
        BundlesQueryType $bundlesQueryType,
        PackagistServiceProviderInterface $packagistServiceProvider,
        FormFactory $formFactory,
        DefaultRouter $router,
        $bundlesListLocationId,
        $bundlesListCardsLimit
    ) {
        $this->templating = $templating;
        $this->searchService = $searchService;
        $this->aliasRouter = $aliasRouter;
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
        $orderForm = $this->formFactory->create(BundleOrderType::class);

        $order = 'default';

        $orderForm->handleRequest($request);
        if ($orderForm->isSubmitted() && $orderForm->isValid()) {
            $order = $orderForm->get('order')->getData();
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
            'order' => $order,
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
        $searchForm = $this->formFactory->create(BundleSearchType::class);
        $searchForm->handleRequest($request);

        if ( !$searchForm->isSubmitted() || !$searchForm->isValid()) {
            return new RedirectResponse($this->aliasRouter->generate('ez_urlalias',
                ['locationId' => $this->bundlesListLocationId], UrlGeneratorInterface::ABSOLUTE_PATH));
        }
        $searchText = $searchForm->get('search')->getData();

        return new RedirectResponse($this->router->generate('_ezplatform_bundles_search_order_list', [
            'searchText' => $searchText,
            'order' => 'default',
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
            'order' => $order,
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
            'search' => $searchText,
            'order' => $order,
        ]);

        $showMoreButton = $searchResults->totalCount > ($offset + count($searchResults->searchHits)) ? true : false;

        return new JsonResponse([
            'html' => $renderedContent,
            'showLoadMoreButton' => $showMoreButton,
        ]);
    }

    /**
     * @param string $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderSortOrderBundleForm($order)
    {
        $sortOrderBundleForm = $this->formFactory->create(BundleOrderType::class, [
            'order' => $order,
        ]);

        return $this->templating->renderResponse(
            '@ezdesign/form/bundle_sort_order.html.twig',
            [
                'sortOrderBundleForm' => $sortOrderBundleForm->createView(),
            ]
        )->setPrivate();
    }

    /**
     * @param string $searchText
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderSearchBundleForm($searchText)
    {
        $searchBundleForm = $this->formFactory->create(BundleSearchType::class, [
            'search' => $searchText,
        ]);

        return $this->templating->renderResponse(
            '@ezdesign/form/bundle_search.html.twig',
            [
                'searchBundleForm' => $searchBundleForm->createView(),
            ]
        )->setPrivate();
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
            'search' => $searchText,
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
                'packageDetails' => $this->packagistServiceProvider->getPackageDetails($searchHit->valueObject->contentInfo->name),
            ];
        }

        return $bundles;
    }
}
