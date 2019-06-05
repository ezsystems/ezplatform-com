<?php

/**
 * PackagistUrlConstraint - Custom Constraint Class for checking package url.
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
class PackagistUrl extends Constraint
{
    public $message = 'We can\'t find this package on packagist.org. Please check that the URL you provided is correct.';
}
