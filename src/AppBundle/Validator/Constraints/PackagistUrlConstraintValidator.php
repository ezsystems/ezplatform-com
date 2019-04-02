<?php

/**
 * PackagistUrlConstraintValidator - Custom Form Constraint Validator Class for validation package url.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Validator\Constraints;

use AppBundle\Service\Packagist\PackagistServiceProviderInterface;
use AppBundle\ValueObject\RepositoryMetadata;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PackageUrlConstraintValidator.
 */
class PackagistUrlConstraintValidator extends ConstraintValidator
{
    /** @var \AppBundle\QueryType\PackagesQueryType */
    private $searchService;

    /** @var \AppBundle\Service\Packagist\PackagistServiceProviderInterface */
    private $packagistServiceProvider;

    public function __construct(
        SearchServiceInterface $searchService,
        PackagistServiceProviderInterface $packagistServiceProvider
    ) {
        $this->searchService = $searchService;
        $this->packagistServiceProvider = $packagistServiceProvider;
    }

    /**
     * ({@inheritdoc})
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PackagistUrlConstraint) {
            throw new UnexpectedTypeException($constraint, PackagistUrlConstraint::class);
        }

        if (!$value) {
            return;
        }

        $repositoryMetadata = new RepositoryMetadata($value);
        $repositoryId = $repositoryMetadata->getRepositoryId();

        if (!$this->packagistServiceProvider->getPackageDetails($repositoryId)) {
            $this->context
                ->buildViolation($constraint->message, [])
                ->addViolation();
        }
    }
}
