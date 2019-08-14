<?php

/**
 * PackagistUrlConstraintValidatorTest - Test Cases for Custom Form Constraint Validator Class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Service\Packagist\PackagistServiceInterface;
use AppBundle\Tests\Objects\InvalidConstraintTypeFixture;
use AppBundle\Validator\Constraints\PackagistUrl;
use AppBundle\Validator\Constraints\PackagistUrlValidator;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;

class PackagistUrlValidatorTest extends AbstractValidator
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|PackagistUrl */
    private $constraintMock;

    /** @var \AppBundle\Validator\Constraints\PackagistUrlValidator */
    private $packagistUrlConstraintValidator;

    /** @var \PHPUnit\Framework\MockObject\MockObject|PackagistServiceInterface */
    private $packagistServiceMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|SearchServiceInterface */
    private $searchServiceMock;

    protected function setUp()
    {
        parent::setUp();

        $this->constraintMock = $this->getMockBuilder(PackagistUrl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchServiceMock = $this->getMockBuilder(SearchServiceInterface::class)->getMock();
        $this->packagistServiceMock = $this->getMockBuilder(PackagistServiceInterface::class)->getMock();
        $this->packagistUrlConstraintValidator = new PackagistUrlValidator(
            $this->searchServiceMock,
            $this->packagistServiceMock
        );
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackagistUrlValidator
     */
    public function testCreatePackageCategoryIdConstraintValidatorInstance()
    {
        $this->assertInstanceOf(PackagistUrlValidator::class, $this->packagistUrlConstraintValidator);
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackagistUrlValidator::validate
     *
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testThrowExceptionWhenConstraintIsNotPackagistUrlConstraint()
    {
        $this->packagistUrlConstraintValidator->initialize($this->getExecutionContextMock());
        $this->packagistUrlConstraintValidator->validate(
            '',
            new InvalidConstraintTypeFixture()
        );
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackagistUrlValidator::validate
     */
    public function testBuildViolationWhenPackagistUrlIsInvalid()
    {
        $this->packagistServiceMock
            ->expects($this->once())
            ->method('getPackageDetails')
            ->with('bundle/not-existing-bundle')
            ->willReturn(null);

        $this->getExecutionContextMock()
            ->expects($this->once())
            ->method('buildViolation')
            ->with('We can\'t find this package on packagist.org. Please check that the URL you provided is correct.')
            ->willReturn($this->getConstraintViolationBuilderMock());

        $this->getConstraintViolationBuilderMock()
            ->expects($this->once())
            ->method('addViolation')
            ->willReturn(null);

        $this->packagistUrlConstraintValidator->initialize($this->getExecutionContextMock());
        $this->packagistUrlConstraintValidator->validate(
            'https://packagist.org/packages/bundle/not-existing-bundle',
            $this->constraintMock
        );
    }
}
