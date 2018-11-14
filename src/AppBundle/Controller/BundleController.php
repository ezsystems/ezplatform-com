<?php

namespace AppBundle\Controller;

use AppBundle\Form\BundleOrderType;
use AppBundle\Form\BundleSearchType;
use AppBundle\QueryType\BundlesQueryType;
use AppBundle\Service\Packagist\PackagistServiceProviderInterface;
use eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter;
use eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchHitAdapter;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

class BundleController
{
    private const DEFAULT_ORDER_CLAUSE = 'default';

    private const DEFAULT_BUNDLES_CATEGORY = 'all';

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
     * @var \Netgen\TagsBundle\API\Repository\TagsService;
     */
    private $tagsService;

    /**
     * @var int
     */
    private $bundlesListLocationId;

    /**
     * @var int
     */
    private $bundlesListCardsLimit;

    /**
     * @var int
     */

    private $bundleCategoriesId;
    /**
     * @var array
     */
    private $relatedBundles;



    /**
     * BundleController constructor.
     * @param EngineInterface $templating
     * @param SearchService $searchService
     * @param UrlAliasRouter $aliasRouter
     * @param BundlesQueryType $bundlesQueryType
     * @param PackagistServiceProviderInterface $packagistServiceProvider
     * @param FormFactory $formFactory
     * @param DefaultRouter $router
     * @param TagsService $tagsService
     * @param int $bundlesListLocationId
     * @param int $bundlesListCardsLimit
     * @param int $bundleCategoriesId
     */
    public function __construct(
        EngineInterface $templating,
        SearchService $searchService,
        UrlAliasRouter $aliasRouter,
        BundlesQueryType $bundlesQueryType,
        PackagistServiceProviderInterface $packagistServiceProvider,
        FormFactory $formFactory,
        DefaultRouter $router,
        TagsService $tagsService,
        int $bundlesListLocationId,
        int $bundlesListCardsLimit,
        int $bundleCategoriesId
    ) {
        $this->templating = $templating;
        $this->searchService = $searchService;
        $this->aliasRouter = $aliasRouter;
        $this->bundlesQueryType = $bundlesQueryType;
        $this->packagistServiceProvider = $packagistServiceProvider;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->tagsService = $tagsService;
        $this->bundlesListLocationId = $bundlesListLocationId;
        $this->bundlesListCardsLimit = $bundlesListCardsLimit;
        $this->bundleCategoriesId = $bundleCategoriesId;
    }

    /**
     * Renders full view `bundle_list`.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $category
     * @param int $page
     * @param string $order
     * @param string $searchText
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Twig\Error\Error
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function showBundlesListAction(Request $request, string $category = self::DEFAULT_BUNDLES_CATEGORY, $page = 1, $order = self::DEFAULT_ORDER_CLAUSE, $searchText = '')
    {
        $orderForm = $this->formFactory->create(BundleOrderType::class);
        $orderForm->handleRequest($request);
        if ($orderForm->isSubmitted() && $orderForm->isValid()) {
            $order = $orderForm->get('order')->getData();
        }

        $searchForm = $this->formFactory->create(BundleSearchType::class);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() || $searchForm->isValid()) {
            $searchText = $searchForm->get('search')->getData();
        }

        if ($category && $category !== self::DEFAULT_BUNDLES_CATEGORY) {
            $this->relatedBundles = $this->getRelatedBundles($category);
        }

        // Get content of Bundles List page
        $query = new Query();
        $criterion = new Query\Criterion\LocationId($this->bundlesListLocationId);

        $query->filter = $criterion;

        $contentSearchResult = $this->searchService->findContent($query);
        $content = $contentSearchResult->searchHits[0]->valueObject;

        // Create pager
        $adapter = new ContentSearchHitAdapter($this->getBundlesQuery(0, $order, $searchText), $this->searchService);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($this->bundlesListCardsLimit);
        $pagerfanta->setCurrentPage($page);

        // Get list of bundles using already fetched data from pager
        $bundles = $this->getList($adapter->getSlice(($page - 1) * $this->bundlesListCardsLimit, $this->bundlesListCardsLimit));

        return $this->templating->renderResponse('@ezdesign/full/bundle_list.html.twig', [
            'items' => $bundles,
            'content' => $content,
            'viewType' => 'full',
            'order' => $order,
            'pager' => $pagerfanta,
            'searchText' => $searchText,
            'bundlesCategories' => $this->getBundlesCategoriesList($this->bundleCategoriesId),
            'selectedBundleCategory' => $category !== self::DEFAULT_BUNDLES_CATEGORY ? $category : ''
        ]);
    }

    /**
     * Validates search query.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function searchBundlesAction(Request $request)
    {
        $searchForm = $this->formFactory->create(BundleSearchType::class);
        $searchForm->handleRequest($request);

        if (!$searchForm->isSubmitted() || !$searchForm->isValid()) {
            return new RedirectResponse($this->aliasRouter->generate('ez_urlalias',
                ['locationId' => $this->bundlesListLocationId], UrlGeneratorInterface::ABSOLUTE_PATH));
        }
        $searchText = $searchForm->get('search')->getData();

        return new RedirectResponse($this->router->generate('_ezplatform_bundles_search', [
            'page' => 1,
            'order' => self::DEFAULT_ORDER_CLAUSE,
            'searchText' => $searchText,
            'category' => self::DEFAULT_BUNDLES_CATEGORY
        ]));
    }

    /**
     * @param string $order
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Twig\Error\Error
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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Twig\Error\Error
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
     * Returns list with bundles categories
     *
     * @var int $categoryId
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getBundlesCategoriesList(int $categoryId): array
    {
        $tag = $this->tagsService->loadTag($categoryId);

        return $this->tagsService->loadTagChildren($tag);
    }

    /**
     * @param string $category
     * @param string $language
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getRelatedBundles(string $category, string $language = ''): array
    {
        $tags = $this->tagsService->loadTagsByKeyword($category, $language);

        $tag = array_filter($tags, function(Tag $tag) {
            return $tag->parentTagId === $this->bundleCategoriesId;
        });

        $relatedContent = $this->tagsService->getRelatedContent(reset($tag));

        return $relatedContent ? array_column($relatedContent, 'id') : [0];
    }

    /**
     * @param int $offset
     * @param null $order
     * @param string $searchText
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationQuery
     */
    private function getBundlesQuery($offset = 0, $order = null, $searchText = '')
    {
        return $this->bundlesQueryType->getQuery([
            'parent_location_id' => $this->bundlesListLocationId,
            'limit' => $this->bundlesListCardsLimit,
            'offset' => $offset,
            'order' => $order,
            'search' => $searchText,
            'contents_id' => $this->relatedBundles
        ]);
    }

    /**
     * Returns list of bundles with package details for given $searchResult set.
     *
     * @param array $searchHits
     *
     * @return array
     */
    private function getList(array $searchHits)
    {
        $bundles = [];
        foreach ($searchHits as $searchHit) {
            $bundles[] = [
                'bundle' => $searchHit,
                'packageDetails' => $this->packagistServiceProvider->getPackageDetails($searchHit->valueObject->contentInfo->name),
            ];
        }

        return $bundles;
    }
}
