<?php

/**
 * PackageController
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Event\AddPackageEvent;
use AppBundle\Form\PackageAddType;
use AppBundle\Form\PackageOrderType;
use AppBundle\Form\PackageSearchType;
use AppBundle\QueryType\PackagesQueryType;
use AppBundle\Service\Package\PackageServiceInterface;
use eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter;
use eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchHitAdapter;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class PackageController
 *
 * @package AppBundle\Controller
 */
class PackageController
{
    private const DEFAULT_ORDER_CLAUSE = 'default';

    private const DEFAULT_PACKAGE_CATEGORY = 'all';

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

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
     * @var \AppBundle\QueryType\PackagesQueryType
     */
    private $packagesQueryType;

    /**
     * @var PackageServiceInterface
     */
    private $packageService;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    private $formFactory;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter
     */
    private $router;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService;
     */
    private $tagsService;

    /**
     * @var int
     */
    private $packageListLocationId;

    /**
     * @var int
     */
    private $packageListCardsLimit;

    /**
     * @var int
     */
    private $packageCategoriesParentTagId;

    /**
     * PackageController constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param EngineInterface $templating
     * @param SearchServiceInterface $searchService
     * @param UrlAliasRouter $aliasRouter
     * @param PackagesQueryType $packagesQueryType
     * @param PackageServiceInterface $packageService
     * @param FormFactory $formFactory
     * @param DefaultRouter $router
     * @param TagsService $tagsService
     * @param LocationService $locationService
     * @param int $packageListLocationId
     * @param int $packageListCardsLimit
     * @param int $packageCategoriesParentTagId
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EngineInterface $templating,
        SearchServiceInterface $searchService,
        UrlAliasRouter $aliasRouter,
        PackagesQueryType $packagesQueryType,
        PackageServiceInterface $packageService,
        FormFactory $formFactory,
        DefaultRouter $router,
        TagsService $tagsService,
        LocationService $locationService,
        int $packageListLocationId,
        int $packageListCardsLimit,
        int $packageCategoriesParentTagId
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->templating = $templating;
        $this->searchService = $searchService;
        $this->aliasRouter = $aliasRouter;
        $this->packagesQueryType = $packagesQueryType;
        $this->packageService = $packageService;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->tagsService = $tagsService;
        $this->locationService = $locationService;
        $this->packageListLocationId = $packageListLocationId;
        $this->packageListCardsLimit = $packageListCardsLimit;
        $this->packageCategoriesParentTagId = $packageCategoriesParentTagId;
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Twig\Error\Error
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function addPackageAction(Request $request): Response
    {
        $addForm = $this->formFactory->create(PackageAddType::class, [
                'package_categories' => $this->getPackageCategoriesList(),
                'packageListLocationId' => $this->packageListLocationId
            ]
        );
        $addForm->handleRequest($request);

        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $content = $this->packageService->addPackage($addForm->getData());

            if ($content) {
                $this->eventDispatcher->dispatch(AddPackageEvent::EVENT_NAME, new AddPackageEvent($content));

                return $this->templating->renderResponse('@ezdesign/full/package_submit_success.html.twig', [
                    'content' => $content
                ]);
            }
        }

        return $this->templating->renderResponse('@ezdesign/full/package_add.html.twig', [
            'addForm' => $addForm->createView()
        ]);
    }

    /**
     * Renders full view `package_list`.
     *
     * @param Request $request
     * @param string $category
     * @param int $page
     * @param string $order
     * @param string $searchText
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Twig\Error\Error
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function showPackageListAction(
        Request $request,
        string $category = self::DEFAULT_PACKAGE_CATEGORY,
        $page = 1, $order = self::DEFAULT_ORDER_CLAUSE,
        $searchText = ''
    ): Response
    {
        $orderForm = $this->formFactory->create(PackageOrderType::class);
        $orderForm->handleRequest($request);
        if ($orderForm->isSubmitted() && $orderForm->isValid()) {
            $order = $orderForm->get('order')->getData();
        }

        $searchForm = $this->formFactory->create(PackageSearchType::class);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() || $searchForm->isValid()) {
            $searchText = $searchForm->get('search')->getData();
        }

        $tagId = null;

        if ($category && $category !== self::DEFAULT_PACKAGE_CATEGORY) {
            $tagId = $this->getCategoryTagId($category);
        }

        $content = $this->locationService->loadLocation($this->packageListLocationId)->getContent();

        // Create pager
        $adapter = new ContentSearchHitAdapter($this->getPackagesQuery(0, $order, $searchText, $tagId), $this->searchService);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($this->packageListCardsLimit);
        $pagerfanta->setCurrentPage($page);

        // Get list of packages using already fetched data from pager
        $packages = $this->getList($adapter->getSlice(($page - 1) * $this->packageListCardsLimit, $this->packageListCardsLimit));

        return $this->templating->renderResponse('@ezdesign/full/package_list.html.twig', [
            'items' => $packages,
            'content' => $content,
            'order' => $order,
            'pager' => $pagerfanta,
            'searchText' => $searchText,
            'packageCategories' => $this->getPackageCategoriesList(),
            'selectedPackageCategory' => $category !== self::DEFAULT_PACKAGE_CATEGORY ? mb_strtolower($category) : ''
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Twig\Error\Error
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getPackageDetailsAction(Request $request): Response
    {
        $content = $this->locationService->loadLocation($request->get('locationId'))->getContent();
        $this->packageService->getPackage($content->getName());

        return $this->templating->renderResponse('@ezdesign/full/package.html.twig', [
            'content' => $content,
            'package' => $this->packageService->getPackage($content->getName())
        ]);
    }

    /**
     * Validates search query.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function searchPackagesAction(Request $request): RedirectResponse
    {
        $searchForm = $this->formFactory->create(PackageSearchType::class);
        $searchForm->handleRequest($request);

        if (!$searchForm->isSubmitted() || !$searchForm->isValid()) {
            return new RedirectResponse($this->aliasRouter->generate('ez_urlalias',
                ['locationId' => $this->packageListLocationId], UrlGeneratorInterface::ABSOLUTE_PATH));
        }
        $searchText = $searchForm->get('search')->getData();

        return new RedirectResponse($this->router->generate('_ezplatform_package_list_search', [
            'page' => 1,
            'order' => self::DEFAULT_ORDER_CLAUSE,
            'searchText' => $searchText,
            'category' => self::DEFAULT_PACKAGE_CATEGORY
        ]));
    }

    /**
     * @param string $order
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Twig\Error\Error
     */
    public function renderSortOrderPackageForm(string $order): Response
    {
        $sortOrderPackageForm = $this->formFactory->create(PackageOrderType::class, [
            'order' => $order,
        ]);

        return $this->templating->renderResponse(
            '@ezdesign/form/package_sort_order.html.twig',
            [
                'sortOrderPackageForm' => $sortOrderPackageForm->createView(),
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
    public function renderSearchPackageForm(string $searchText): Response
    {
        $searchPackageForm = $this->formFactory->create(PackageSearchType::class, [
            'search' => $searchText,
        ]);

        return $this->templating->renderResponse(
            '@ezdesign/form/package_search.html.twig',
            [
                'searchPackageForm' => $searchPackageForm->createView(),
            ]
        )->setPrivate();
    }

    /**
     * Returns list with package categories
     *
     * @var int $categoryId
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getPackageCategoriesList(): array
    {
        $tag = $this->tagsService->loadTag($this->packageCategoriesParentTagId);

        return $this->tagsService->loadTagChildren($tag);
    }

    /**
     * @param string $category
     * @param string $language
     *
     * @return int|null
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getCategoryTagId(string $category, string $language = ''): ?int
    {
        $tags = $this->tagsService->loadTagsByKeyword($category, $language);

        $tags = array_filter($tags, function(Tag $tag) {
            return mb_strtolower($tag->parentTagId) === mb_strtolower($this->packageCategoriesParentTagId);
        });

        $tag = reset($tags);

        return $tag ? $tag->id : null;
    }

    /**
     * @param int $offset
     * @param null $order
     * @param string $searchText
     * @param int $tagId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationQuery
     */
    private function getPackagesQuery($offset = 0, $order = null, $searchText = '', $tagId = null): LocationQuery
    {
        return $this->packagesQueryType->getQuery([
            'parent_location_id' => $this->packageListLocationId,
            'limit' => $this->packageListCardsLimit,
            'offset' => $offset,
            'order' => $order,
            'search' => $searchText,
            'tag_id' => $tagId
        ]);
    }

    /**
     * Returns list of packages with package details for given $searchResult set.
     *
     * @param array $searchHits
     *
     * @return array
     */
    private function getList(array $searchHits): array
    {
        $packages = [];
        foreach ($searchHits as $searchHit) {
            $packages[] = [
                'package' => $searchHit,
                'packageDetails' => $this->packageService->getPackage($searchHit->valueObject->contentInfo->name)
            ];
        }

        return $packages;
    }
}
