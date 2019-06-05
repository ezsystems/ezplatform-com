<?php

/**
 * PackageCategoryIdConstraintTest - Test Cases for Custom Form Constraint Class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Validator\Constraints\PackageCategoryId;
use PHPUnit\Framework\TestCase;

class PackageCategoryIdTest extends TestCase
{
    /**
     * @covers \AppBundle\Validator\Constraints\PackageCategoryId
     */
    public function testCreatePackageCategoryIdConstraintInstance()
    {
        $packageCategoryIdConstraint = $this->getMockBuilder(PackageCategoryId::class)
            ->disableOriginalConstructor()->getMock();

        $this->assertInstanceOf(PackageCategoryId::class, $packageCategoryIdConstraint);
    }
}
