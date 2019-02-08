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
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * Class PackageCategoryIdConstraint
 *
 * @package AppBundle\Validator\Constraints
 */
class PackageCategoryIdConstraint extends Constraint
{
    public $message = 'Following category ids does not exists: {{ categories }}';

    /**
     * @var array
     */
    private $packageCategoryIds = [];

    /**
     * PackageCategoryIdConstraint constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (!isset($options['categories'])) {
            throw new MissingOptionsException('Required array option \'categories\' is missing', []);
        }

        $this->packageCategoryIds = $options['categories'];
    }

    /**
     * @return array
     */
    public function getPackageCategoryIds(): array
    {
        return $this->packageCategoryIds;
    }
}
