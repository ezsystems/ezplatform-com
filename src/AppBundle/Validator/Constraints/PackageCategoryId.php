<?php

/**
 * PackageCategoryIdConstraint - Custom Constraint Class for checking package category id.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class PackageCategoryId extends Constraint
{
    public $message = 'Following category ids does not exists: {{ categories }}';
}
