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
 * Class PackageDbNotExistsConstraint.
 */
class PackageDbNotExistsConstraint extends Constraint
{
    public $message = 'This {{ name }} is already in the package catalog.';

    public $messageWaitingForApproval = 'This package is waiting for approval';

    /** @var int */
    private $packageListLocationId = 0;

    /** @var string */
    private $targetField = '';

    public function __construct(array $options)
    {
        if (!isset($options['packageListLocationId'])) {
            throw new MissingOptionsException('Required option \'packageListLocationId\' is missing', []);
        }

        if (!isset($options['targetField'])) {
            throw new MissingOptionsException('Required option \'targetField\' is missing', []);
        }

        $this->packageListLocationId = $options['packageListLocationId'];
        $this->targetField = $options['targetField'];
    }

    /** @return int|mixed */
    public function getPackageListLocationId(): int
    {
        return $this->packageListLocationId;
    }

    /** @return string */
    public function getTargetField(): string
    {
        return $this->targetField;
    }
}
