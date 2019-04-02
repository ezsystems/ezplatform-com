<?php

/**
 * PackageCategoryIdConstraintValidator - Custom Form Constraint Validator Class for validation package category id.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PackageCategoryIdConstraintValidator.
 */
class PackageCategoryIdConstraintValidator extends ConstraintValidator
{
    /**
     * ({@inheritdoc})
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PackageCategoryIdConstraint) {
            throw new UnexpectedTypeException($constraint, PackageCategoryIdConstraint::class);
        }

        if (!$value) {
            $this->context
                ->buildViolation('This value should not be null.')
                ->addViolation();
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if (count(array_intersect($value, $constraint->getPackageCategoryIds())) !== count($value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ categories }}', implode(', ', array_diff($value, $constraint->getPackageCategoryIds())))
                ->addViolation();
        }
    }
}
