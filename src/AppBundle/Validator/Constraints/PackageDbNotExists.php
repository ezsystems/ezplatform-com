<?php

/**
 * PackageDbNotExistsConstraint - Custom Constraint Class for checking is package exists in catalog.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class PackageDbNotExists extends Constraint
{
    /** @var string */
    public $message = 'This {{ name }} is already in the package catalog.';

    /** @var string */
    public $messageWaitingForApproval = 'This package is waiting for approval';

    /** @var string */
    public $targetField = '';

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (!isset($options['targetField'])) {
            throw new MissingOptionsException('Required option \'targetField\' is missing', []);
        }

        $this->targetField = $options['targetField'];

        parent::__construct($options);
    }
}
