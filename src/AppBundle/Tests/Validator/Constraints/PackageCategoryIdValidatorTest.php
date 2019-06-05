<?php

/**
 * PackageCategoryIdConstraintValidatorTest - Test Cases for Custom Form Constraint Validator Class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Helper\PackageCategoryListHelper;
use AppBundle\Tests\Objects\InvalidConstraintTypeFixture;
use AppBundle\Validator\Constraints\PackageCategoryId;
use AppBundle\Validator\Constraints\PackageCategoryIdValidator;
use Symfony\Component\Validator\Constraint;

class PackageCategoryIdValidatorTest extends AbstractValidator
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|Constraint|PackageCategoryId */
    private $constraintMock;

    /** @var \AppBundle\Validator\Constraints\PackageCategoryIdValidator */
    private $packageCategoryIdConstraintValidator;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\AppBundle\Helper\PackageCategoryListHelper */
    private $packageCategoryListHelperMock;

    /** @var array */
    private $categories;

    /** @var array */
    private $invalidCategories;

    protected function setUp()
    {
        parent::setUp();

        $this->constraintMock = $this->getMockBuilder(PackageCategoryId::class)
            ->disableOriginalConstructor()
            ->setConstructorArgs([['categories' => $this->categories]])
            ->getMock();

        $this->packageCategoryListHelperMock = $this->getMockBuilder(PackageCategoryListHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->packageCategoryIdConstraintValidator = new PackageCategoryIdValidator($this->packageCategoryListHelperMock);
        $this->categories = [2, 4, 6];
        $this->invalidCategories = [5, 10, 15];
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageCategoryIdValidator
     */
    public function testCreatePackageCategoryIdConstraintValidatorInstance()
    {
        $this->assertInstanceOf(PackageCategoryIdValidator::class, $this->packageCategoryIdConstraintValidator);
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageCategoryIdValidator::validate()
     *
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testThrowExceptionWhenConstraintIsNotPackageCategoryIdConstraint()
    {
        $this->packageCategoryIdConstraintValidator->initialize($this->getExecutionContextMock());
        $this->packageCategoryIdConstraintValidator->validate(
            '',
            new InvalidConstraintTypeFixture()
        );
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageCategoryIdValidator::validate()
     */
    public function testBuildViolationWhenValidationValueIsNull()
    {
        $this->getExecutionContextMock()
            ->expects($this->once())
            ->method('buildViolation')
            ->with('This value should not be null.')
            ->willReturn($this->getConstraintViolationBuilderMock());

        $this->getConstraintViolationBuilderMock()
            ->expects($this->once())
            ->method('addViolation')
            ->willReturn(null);

        $this->packageCategoryIdConstraintValidator->initialize($this->getExecutionContextMock());
        $this->packageCategoryIdConstraintValidator->validate(
            [],
            $this->constraintMock
        );
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageCategoryIdValidator::validate()
     *
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testThrowExceptionWhenPassedValueIsNotArray()
    {
        $this->getExecutionContextMock()
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($this->getConstraintViolationBuilderMock());

        $this->packageCategoryIdConstraintValidator->initialize($this->getExecutionContextMock());
        $this->packageCategoryIdConstraintValidator->validate(
            '',
            $this->constraintMock
        );
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageCategoryIdValidator::validate()
     */
    public function testBuildViolationWhenPackageCategoryIdIsInvalid()
    {
        $this->packageCategoryListHelperMock
            ->expects($this->any())
            ->method('getPackageCategoryList')
            ->willReturn($this->categories);

        $this->getExecutionContextMock()
            ->expects($this->once())
            ->method('buildViolation')
            ->with('Following category ids does not exists: {{ categories }}')
            ->willReturn($this->getConstraintViolationBuilderMock());

        $this->getConstraintViolationBuilderMock()
            ->expects($this->once())
            ->method('setParameter')
            ->with('{{ categories }}', implode(', ', $this->invalidCategories))
            ->willReturn($this->getConstraintViolationBuilderMock());

        $this->getConstraintViolationBuilderMock()
            ->expects($this->once())
            ->method('addViolation')
            ->willReturn(null);

        $this->packageCategoryIdConstraintValidator->initialize($this->getExecutionContextMock());
        $this->packageCategoryIdConstraintValidator->validate(
            array_merge($this->categories, $this->invalidCategories),
            $this->constraintMock
        );
    }
}
