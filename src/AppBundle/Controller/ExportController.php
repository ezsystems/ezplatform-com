<?php

namespace AppBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Repository;
use EzSystems\EzPlatformRichText\eZ\RichText\Converter as RichTextConverterInterface;

class ExportController extends Controller
{
    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController */
    private $viewController;

    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $repository;

    private $urlAliasGenerator;

    private $tagService;

    private $richTextOutputConverter;

    public function __construct(
        RequestStack $requestStack,
        ViewController $viewController,
        UserService $userService,
        ContentTypeService $contentTypeService,
        SearchService $searchService,
        LocationService $locationService,
        TagsService $tagService,
        UrlAliasGenerator $urlAliasGenerator,
        Repository $repository,
        RichTextConverterInterface $richTextOutputConverter
    ) {
        $this->requestStack = $requestStack;
        $this->viewController = $viewController;
        $this->userService = $userService;
        $this->contentTypeService = $contentTypeService;
        $this->searchService = $searchService;
        $this->locationService = $locationService;
        $this->tagService = $tagService;
        $this->repository = $repository;
        $this->urlAliasGenerator = $urlAliasGenerator;
        $this->richTextOutputConverter = $richTextOutputConverter;
    }

    public function exportAction($contentTypeId){

        $payload = [''];

        if($contentTypeId == 'package') {
            $payload = $this->getPackages();
        }else if($contentTypeId == 'security_advisory') {
            $payload = $this->getSecurityAdvisories();
        }

        $response = new JsonResponse($payload);
        return $response;
    }

    private function getPackages(){

        $contentTypeId = 26;
        $parentLocationId = 168;

        $items = $this->getObjectData($parentLocationId,$contentTypeId);

        return $items;

    }

    private function getSecurityAdvisories(){

        $contentTypeId = 32;
        $parentLocationId = 629;

        $items = $this->getObjectData($parentLocationId,$contentTypeId);

        return $items;

    }

    public function fieldValueParser($object, $identifier){

        $fieldValue = $object->getFieldValue($identifier);
        $customFieldValue = [];

        if($fieldValue instanceof \EzSystems\EzPlatformRichText\eZ\FieldType\RichText\Value){
            $fieldValue = $this->richTextOutputConverter->convert($fieldValue->xml)->saveHTML();
        } else if($fieldValue instanceof \Netgen\TagsBundle\Core\FieldType\Tags\Value){
            $firstTag = $this->tagService->loadTag($fieldValue->tags[0]->id);
            $fieldValue =  $firstTag->getKeyword();
        }

        return $fieldValue;

    }

    public function getObjectData($parentLocationId,$contentTypeId){

        $language = 'eng-GB';

        if(isset($_GET['parent_id'])){
            $parentLocationId = (int) $_GET['parent_id'];
        }

        if(isset($_GET['language'])){
            $language = $_GET['language'];
        } 

        $query = new \eZ\Publish\API\Repository\Values\Content\Query();

        $criteria = [];

        $criteria[] = new Criterion\Subtree( $this->locationService->loadLocation( $parentLocationId )->pathString );
        $criteria[] = new Criterion\ContentTypeId( $contentTypeId );
        $criteria[] = new Criterion\Visibility(Criterion\Visibility::VISIBLE);
        $criteria[] = new Criterion\LanguageCode($language);

        $query->filter = new Criterion\LogicalAnd(
            $criteria
        );

        $query->sortClauses = [
            new SortClause\DatePublished(Query::SORT_DESC)
        ];

        $query->limit = 10000;

        $result = $this->searchService->findContent( $query );

        foreach($result->searchHits as $searchHit){
            $contentObject = $searchHit->valueObject;
            $location = $this->locationService->loadLocation($contentObject->versionInfo->contentInfo->mainLocationId);

            $objectData = [
                'objectid' => $searchHit->valueObject->id,
                'urlpath' => $this->urlAliasGenerator->getPathPrefixByRootLocationId($location->id)
            ];

            foreach($contentObject->getFields() as $field){
                $objectData[$field->fieldDefIdentifier] =  $this->fieldValueParser($contentObject,$field->fieldDefIdentifier);
            }

            $items[] = $objectData;

        }

        return $items;

    }

}
