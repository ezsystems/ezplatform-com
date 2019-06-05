<?php

/**
 * PackageDbNotExistsConstraintTest - Test Cases for Custom Form Constraint Class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Validator\Constraints\PackageDbNotExists;
use PHPUnit\Framework\TestCase;

class PackageDbNotExistsTest extends TestCase
{
    /** @var PackageDbNotExists */
    private $packageDbNotExistsConstraint;

    /** @var string */
    private $targetField;

    protected function setUp()
    {
        $this->targetField = 'field_name';

        $constructorArguments = [
            'targetField' => $this->targetField,
        ];

        $this->packageDbNotExistsConstraint = new PackageDbNotExists($constructorArguments);
    }

    /**
     * @covers \AppBundle\Validator\Constraints\PackageDbNotExists
     */
    public function testCreatePackageDbNotExistsConstraintInstance()
    {
        $packageDbNotExistsConstraint = $this->getMockBuilder(PackageDbNotExists::class)
            ->disableOriginalConstructor()->getMock();

        $this->assertInstanceOf(PackageDbNotExists::class, $packageDbNotExistsConstraint);
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
        new PackageDbNotExists($constructorArguments);
    }

    /** @return iterable */
    public function constructorArgumentsProvider(): iterable
    {
        return [
            [['targetField', 'field_name']],
            [['test', 'arrayKey']],
            [[]],
        ];
    }
}
