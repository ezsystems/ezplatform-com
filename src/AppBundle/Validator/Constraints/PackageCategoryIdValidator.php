<?php

/**
 * PackageCategoryIdConstraintValidator - Custom Form Constraint Validator Class for validation package category id.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Validator\Constraints;

use AppBundle\Helper\PackageCategoryListHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PackageCategoryIdValidator extends ConstraintValidator
{
    /** @var \AppBundle\Helper\PackageCategoryListHelper */
    private $categoryListHelper;

    /**
     * @param \AppBundle\Helper\PackageCategoryListHelper $categoryListHelper
     */
    public function __construct(PackageCategoryListHelper $categoryListHelper)
    {
        $this->categoryListHelper = $categoryListHelper;
    }

    /**
     * ({@inheritdoc})
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PackageCategoryId) {
            throw new UnexpectedTypeException($constraint, PackageCategoryId::class);
        }

        if (!$value) {
            $this->context
                ->buildViolation('This value should not be null.')
                ->addViolation();
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        $packageCategoryListIds = $this->categoryListHelper->getPackageCategoryList();

        if (count(array_intersect($value, $packageCategoryListIds)) !== count($value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ categories }}', implode(', ', array_diff($value, $packageCategoryListIds)))
                ->addViolation();
        }
    }
}
