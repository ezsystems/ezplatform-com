<?php

/**
 * PackageCategoryIdConstraintValidatorTest - Test Cases for Custom Form Constraint Validator Class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Tests\Fixtures\InvalidConstraintTypeFixture;
use AppBundle\Validator\Constraints\PackageCategoryIdConstraint;
use AppBundle\Validator\Constraints\PackageCategoryIdConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Class PackageCategoryIdConstraintValidatorTest
 *
 * @package AppBundle\Tests\Validator\Constraints
 */
class PackageCategoryIdConstraintValidatorTest extends AbstractConstraintValidator
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Constraint|PackageCategoryIdConstraint
     */
    private $constraintMock;

    /**
     * @var \AppBundle\Validator\Constraints\PackageCategoryIdConstraintValidator
     */
    private $packageCategoryIdConstraintValidator;

    /**
     * @var array
     */
    private $categories;

    /**
     * @var array
     */
    private $invalidCategories;

    protected function setUp()
    {
        parent::setUp();

        $this->constraintMock = $this->getMockBuilder(PackageCategoryIdConstraint::class)
            ->disableOriginalConstructor()
            ->setConstructorArgs([['categories' => $this->categories]])
            ->getMock();

        $this->packageCategoryIdConstraintValidator = new PackageCategoryIdConstraintValidator();
        $this->categories = [2,4,6];
        $this->invalidCategories = [5, 10, 15];
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageCategoryIdConstraintValidator
     */
    public function testCreatePackageCategoryIdConstraintValidatorInstance()
    {
        $this->assertInstanceOf(PackageCategoryIdConstraintValidator::class, $this->packageCategoryIdConstraintValidator);
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageCategoryIdConstraintValidator::validate()
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
     * @covers \AppBundle\Validator\Constraints\PackageCategoryIdConstraintValidator::validate()
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
     * @covers \AppBundle\Validator\Constraints\PackageCategoryIdConstraintValidator::validate()
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
     * @covers \AppBundle\Validator\Constraints\PackageCategoryIdConstraintValidator::validate()
     */
    public function testBuildViolationWhenPackageCategoryIdIsInvalid()
    {
        $this->constraintMock
            ->expects($this->any())
            ->method('getPackageCategoryIds')
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
