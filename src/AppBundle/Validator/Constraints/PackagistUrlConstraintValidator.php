<?php

/**
 * PackagistUrlConstraintValidator - Custom Form Constraint Validator Class for validation package url.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Validator\Constraints;

use AppBundle\Service\Packagist\PackagistServiceProviderInterface;
use AppBundle\Url\UrlBuilder;
use AppBundle\ValueObject\RepositoryMetadata;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PackageUrlConstraintValidator
 *
 * @package AppBundle\Validator\Constraints
 */
class PackagistUrlConstraintValidator extends ConstraintValidator
{
    /**
     * @var \AppBundle\QueryType\PackagesQueryType
     */
    private $searchService;

    /**
     * @var \AppBundle\Service\Packagist\PackagistServiceProviderInterface
     */
    private $packagistServiceProvider;

    /**
     * @var \AppBundle\Url\UrlBuilder
     */
    private $urlBuilder;

    public function __construct(
        SearchServiceInterface $searchService,
        PackagistServiceProviderInterface $packagistServiceProvider,
        UrlBuilder $urlBuilder
    ) {
        $this->searchService = $searchService;
        $this->packagistServiceProvider = $packagistServiceProvider;
        $this->urlBuilder = $urlBuilder;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PackagistUrlConstraint) {
            throw new UnexpectedTypeException($constraint, PackagistUrlConstraint::class);
        }

        if (!$value) {
            return;
        }

        $repositoryMetadata = new RepositoryMetadata($value);
        $repositoryId = $this->urlBuilder->urlGlue($repositoryMetadata->getUsername(), $repositoryMetadata->getRepositoryName());

        if (!$this->packagistServiceProvider->getPackageDetails($repositoryId)) {
            $this->context
                ->buildViolation($constraint->message, [])
                ->addViolation();
        }
    }
}
