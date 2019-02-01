<?php

/**
 * PackageDbNotExistsConstraintValidatorTest - Test Cases for Custom Form Constraint Validator Class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Tests\Fixtures\InvalidConstraintTypeFixture;
use AppBundle\Url\UrlBuilder;
use AppBundle\Validator\Constraints\PackageDbNotExistsConstraint;
use AppBundle\Validator\Constraints\PackageDbNotExistsConstraintValidator;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use EzSystems\EzPlatformAdminUi\UI\Dataset\ContentDraftsDataset;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class PackageDbNotExistsConstraintValidatorTest
 *
 * @package AppBundle\Tests\Validator\Constraints
 */
class PackageDbNotExistsConstraintValidatorTest extends AbstractConstraintValidator
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|PackageDbNotExistsConstraint
     */
    private $constraintMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SearchServiceInterface
     */
    private $searchServiceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ContentDraftsDataset
     */
    private $contentDraftsDatasetMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UrlBuilder
     */
    private $urlBuilderMock;

    /**
     * @var PackageDbNotExistsConstraintValidator
     */
    private $packageDbNotExistsConstraintValidator;

    /**
     * @var int
     */
    private $packageListLocationId;

    /**
     * @var string
     */
    private $targetField;

    /**
     * @var string
     */
    private $search;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SearchResult
     */
    private $searchResult;

    protected function setUp()
    {
        parent::setUp();

        $this->packageListLocationId = 123;
        $this->targetField = 'packagist_url';
        $this->search = 'bundle/published-bundle';
        $this->searchResult = $this->getMockBuilder(SearchResult::class)->getMock();
        $this->searchServiceMock = $this->getMockBuilder(SearchServiceInterface::class)->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlBuilder::class)->getMock();

        $this->contentDraftsDatasetMock = $this->getMockBuilder(ContentDraftsDataset::class)
            ->setConstructorArgs([
                $this->getMockBuilder(ContentServiceInterface::class)->getMock(),
                $this->getMockBuilder(ContentTypeServiceInterface::class)->getMock(),
                $this->getMockBuilder(LocationServiceInterface::class)->getMock()
            ])
            ->getMock();

        $this->packageDbNotExistsConstraintValidator = new PackageDbNotExistsConstraintValidator(
            $this->searchServiceMock,
            $this->contentDraftsDatasetMock,
            $this->urlBuilderMock
        );
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageDbNotExistsConstraintValidator
     */
    public function testCreatePackageCategoryIdConstraintValidatorInstance()
    {
        $this->assertInstanceOf(PackageDbNotExistsConstraintValidator::class, $this->packageDbNotExistsConstraintValidator);
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageDbNotExistsConstraintValidator::validate
     *
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testThrowExceptionWhenConstraintIsNotPackagistUrlConstraint()
    {
        $this->packageDbNotExistsConstraintValidator->initialize($this->getExecutionContextMock());
        $this->packageDbNotExistsConstraintValidator->validate(
            '',
            new InvalidConstraintTypeFixture()
        );
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageDbNotExistsConstraintValidator::validate
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testBuildViolationWhenDraftIsWaitingForApproval()
    {
        $constraintMockConstructorArgs = [
            'packageListLocationId' => $this->packageListLocationId,
            'targetField' => $this->targetField
        ];
        $constraintMock = $this->getConstraintMock($constraintMockConstructorArgs);

        $constraintMock
            ->expects($this->any())
            ->method('getPackageListLocationId')
            ->willReturn($this->packageListLocationId);

        $constraintMock
            ->expects($this->any())
            ->method('getTargetField')
            ->willReturn($this->targetField);

        $this->contentDraftsDatasetMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->contentDraftsDatasetMock);

        $this->urlBuilderMock
            ->expects($this->once())
            ->method('urlGlue')
            ->withAnyParameters()
            ->willReturn('bundle/bundle-with-existing-draft');

        $this->contentDraftsDatasetMock
            ->expects($this->once())
            ->method('getContentDrafts')
            ->willReturn([['name' => 'bundle/bundle-with-existing-draft']]);

        $this->getExecutionContextMock()
            ->expects($this->once())
            ->method('buildViolation')
            ->with('This package is waiting for approval')
            ->willReturn($this->getConstraintViolationBuilderMock());

        $this->getConstraintViolationBuilderMock()
            ->expects($this->once())
            ->method('addViolation')
            ->willReturn(null);

        $this->packageDbNotExistsConstraintValidator->initialize($this->getExecutionContextMock());
        $this->packageDbNotExistsConstraintValidator->validate('bundle/bundle-with-existing-draft', $this->constraintMock);
    }

    /**
     * @param string $targetField
     * @param string $messageArg
     *
     * @covers \AppBundle\Validator\Constraints\PackageDbNotExistsConstraintValidator::validate
     *
     * @dataProvider existsPackageProvider()
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     */
    public function testBuildViolationWhenPackageIsPublished(string $targetField, string $messageArg)
    {
        $constraintMockConstructorArgs = [
            'packageListLocationId' => $this->packageListLocationId,
            'targetField' => $targetField
        ];
        $constraintMock = $this->getConstraintMock($constraintMockConstructorArgs);

        $constraintMock
            ->expects($this->any())
            ->method('getPackageListLocationId')
            ->willReturn($this->packageListLocationId);

        $constraintMock
            ->expects($this->any())
            ->method('getTargetField')
            ->willReturn($targetField);

        $this->contentDraftsDatasetMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->contentDraftsDatasetMock);

        $this->urlBuilderMock
            ->expects($this->once())
            ->method('urlGlue')
            ->withAnyParameters()
            ->willReturn($this->search);

        $this->contentDraftsDatasetMock
            ->expects($this->once())
            ->method('getContentDrafts')
            ->willReturn([['name' => 'bundle/bundle-with-existing-draft']]);

        $this->searchServiceMock
            ->expects($this->once())
            ->method('findContentInfo')
            ->with($this->getQuery($targetField))
            ->willReturn($this->searchResult);

        $this->searchResult->totalCount = 1;

        $this->getExecutionContextMock()
            ->expects($this->once())
            ->method('buildViolation')
            ->with('This {{ name }} is already in the package catalog.')
            ->willReturn($this->getConstraintViolationBuilderMock());

        $this->getConstraintViolationBuilderMock()
            ->expects($this->once())
            ->method('setParameter')
            ->with('{{ name }}', $messageArg)
            ->willReturn($this->getConstraintViolationBuilderMock());

        $this->getConstraintViolationBuilderMock()
            ->expects($this->once())
            ->method('addViolation')
            ->willReturn(null);

        $this->packageDbNotExistsConstraintValidator->initialize($this->getExecutionContextMock());
        $this->packageDbNotExistsConstraintValidator->validate($this->search, $this->constraintMock);
    }

    /**
     * @return iterable
     */
    public function existsPackageProvider(): iterable
    {
        return [
            ['packagist_url', 'package'],
            ['name', 'package name'],
        ];
    }

    /**
     * @param array $constraintMockConstructorArgs
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getConstraintMock(array $constraintMockConstructorArgs): MockObject
    {
        return $this->constraintMock = $this->getMockBuilder(PackageDbNotExistsConstraint::class)
            ->disableOriginalConstructor()
            ->setConstructorArgs([$constraintMockConstructorArgs])
            ->getMock();
    }

    /**
     * @param string $targetField
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    private function getQuery(string $targetField): Query
    {
        $query = new Query();

        $query->query = new Query\Criterion\LogicalAnd([
            new Query\Criterion\ParentLocationId($this->packageListLocationId),
            new Query\Criterion\ContentTypeIdentifier('package'),
            new Query\Criterion\Field($targetField, Query\Criterion\Operator::EQ, $this->search)
        ]);

        return $query;
    }

}
