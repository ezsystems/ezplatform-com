<?php

/**
 * PackageCategoryIdConstraintTest - Test Cases for Custom Form Constraint Class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Validator\Constraints\PackageCategoryIdConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Class PackageCategoryIdConstraintTest.
 */
class PackageCategoryIdConstraintTest extends TestCase
{
    /** @covers \AppBundle\Validator\Constraints\PackageCategoryIdConstraint */
    public function testCreatePackageCategoryIdConstraintInstance()
    {
        $packageCategoryIdConstraint = $this->getMockBuilder(PackageCategoryIdConstraint::class)
            ->disableOriginalConstructor()->getMock();

        $this->assertInstanceOf(PackageCategoryIdConstraint::class, $packageCategoryIdConstraint);
    }

    /** @expectedException \Symfony\Component\Validator\Exception\MissingOptionsException */
    public function testThrowExceptionWhenRequireOptionIsMissing()
    {
        new PackageCategoryIdConstraint([]);
    }

    /** @covers \AppBundle\Validator\Constraints\PackageCategoryIdConstraint::getPackageCategoryIds */
    public function testGetPackageCategoryIdsCanReturnArray()
    {
        $categories = [1, 2, 3, 4, 5];
        $packageCategoryIdConstraint = new PackageCategoryIdConstraint(['categories' => $categories]);

        $this->assertEquals($categories, $packageCategoryIdConstraint->getPackageCategoryIds());
    }
}
