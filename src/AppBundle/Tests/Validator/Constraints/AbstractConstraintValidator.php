<?php

/**
 * AbstractConstraintValidator - Provides common Mocks for Constraint Validators test cases.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Validator\Constraints;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * Class AbstractConstraintValidatorTest.
 */
abstract class AbstractConstraintValidator extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $executionContextMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $constraintViolationBuilderMock;

    protected function setUp()
    {
        $this->executionContextMock = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $this->constraintViolationBuilderMock = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)->getMock();
    }

    /** @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Validator\Context\ExecutionContextInterface */
    protected function getExecutionContextMock(): MockObject
    {
        return $this->executionContextMock;
    }

    /** @return \PHPUnit\Framework\MockObject\MockObject|ConstraintViolationBuilderInterface */
    protected function getConstraintViolationBuilderMock(): MockObject
    {
        return $this->constraintViolationBuilderMock;
    }
}
