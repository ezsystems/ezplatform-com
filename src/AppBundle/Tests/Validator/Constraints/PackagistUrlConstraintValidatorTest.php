<?php

/**
 * PackagistUrlConstraintValidatorTest - Test Cases for Custom Form Constraint Validator Class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Service\Packagist\PackagistServiceProviderInterface;
use AppBundle\Tests\Fixtures\InvalidConstraintTypeFixture;
use AppBundle\Validator\Constraints\PackagistUrlConstraint;
use AppBundle\Validator\Constraints\PackagistUrlConstraintValidator;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;

class PackagistUrlConstraintValidatorTest extends AbstractConstraintValidator
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|PackagistUrlConstraint
     */
    private $constraintMock;

    /**
     * @var \AppBundle\Validator\Constraints\PackagistUrlConstraintValidator
     */
    private $packagistUrlConstraintValidator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|PackagistServiceProviderInterface
     */
    private $packagistServiceProviderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SearchServiceInterface
     */
    private $searchServiceMock;

    protected function setUp()
    {
        parent::setUp();

        $this->constraintMock = $this->getMockBuilder(PackagistUrlConstraint::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchServiceMock = $this->getMockBuilder(SearchServiceInterface::class)->getMock();
        $this->packagistServiceProviderMock = $this->getMockBuilder(PackagistServiceProviderInterface::class)->getMock();
        $this->packagistUrlConstraintValidator = new PackagistUrlConstraintValidator(
            $this->searchServiceMock,
            $this->packagistServiceProviderMock
        );
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackagistUrlConstraintValidator
     */
    public function testCreatePackageCategoryIdConstraintValidatorInstance()
    {
        $this->assertInstanceOf(PackagistUrlConstraintValidator::class, $this->packagistUrlConstraintValidator);
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackagistUrlConstraintValidator::validate
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
     * @covers \AppBundle\Validator\Constraints\PackagistUrlConstraintValidator::validate
     */
    public function testBuildViolationWhenPackagistUrlIsInvalid()
    {
        $this->packagistServiceProviderMock
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
