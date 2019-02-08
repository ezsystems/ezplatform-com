<?php

/**
 * PackageDbNotExistsConstraintTest - Test Cases for Custom Form Constraint Class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Validator\Constraints\PackageDbNotExistsConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Class PackageDbNotExistsConstraintTest
 *
 * @package AppBundle\Tests\Validator\Constraints
 */
class PackageDbNotExistsConstraintTest extends TestCase
{
    /**
     * @var PackageDbNotExistsConstraint
     */
    private $packageDbNotExistsConstraint;

    /**
     * @var int
     */
    private $packageListLocationId;

    /**
     * @var string
     */
    private $targetField;

    protected function setUp()
    {
        $this->packageListLocationId = 123;
        $this->targetField = 'field_name';

        $constructorArguments = [
            'packageListLocationId' => $this->packageListLocationId,
            'targetField' => $this->targetField
        ];

        $this->packageDbNotExistsConstraint = new PackageDbNotExistsConstraint($constructorArguments);
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageDbNotExistsConstraint
     */
    public function testCreatePackageDbNotExistsConstraintInstance()
    {
        $packageDbNotExistsConstraint = $this->getMockBuilder(PackageDbNotExistsConstraint::class)
            ->disableOriginalConstructor()->getMock();

        $this->assertInstanceOf(PackageDbNotExistsConstraint::class, $packageDbNotExistsConstraint);
    }

    /**
     * @param array $constructorArguments
     *
     * @dataProvider constructorArgumentsProvider()
     *
     * @expectedException \Symfony\Component\Validator\Exception\MissingOptionsException
     */
    public function testThrowExceptionWhenRequireOptionIsMissing(array $constructorArguments)
    {
        new PackageDbNotExistsConstraint($constructorArguments);
    }

    /**
     * @return iterable
     */
    public function constructorArgumentsProvider(): iterable
    {
        return [
            [['targetField' => 'field_name']],
            [['packageListLocationId' => 123]],
            [['targetField', 'field_name']],
            [['test', 'arrayKey']],
            [[]],
        ];
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageDbNotExistsConstraint::getPackageListLocationId()
     */
    public function testCanGetPackageListLocationId()
    {
        $this->assertEquals($this->packageListLocationId, $this->packageDbNotExistsConstraint->getPackageListLocationId());
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageDbNotExistsConstraint::getTargetField()
     */
    public function testCanGetTargetField()
    {
        $this->assertEquals($this->targetField, $this->packageDbNotExistsConstraint->getTargetField());
    }
}
