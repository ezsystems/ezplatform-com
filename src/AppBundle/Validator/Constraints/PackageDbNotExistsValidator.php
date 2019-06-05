<?php

/**
 * PackageDbNotExistsConstraintValidator - Custom Form Constraint Validator Class for validation if given package exists in catalog.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Validator\Constraints;

use AppBundle\ValueObject\RepositoryMetadata;
use eZ\Publish\API\Repository\PermissionResolver as PermissionResolverInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzPlatformAdminUi\UI\Dataset\ContentDraftsDataset;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PackageDbNotExistsValidator extends ConstraintValidator
{
    private static $VALIDATION_MESSAGE = [
        'packagist_url' => 'package',
        'name' => 'package name',
    ];

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \EzSystems\EzPlatformAdminUi\UI\Dataset\ContentDraftsDataset */
    private $contentDraftsDataset;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /**
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \EzSystems\EzPlatformAdminUi\UI\Dataset\ContentDraftsDataset $contentDraftsDataset
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function __construct(
        PermissionResolverInterface $permissionResolver,
        UserServiceInterface $userService,
        SearchServiceInterface $searchService,
        ContentDraftsDataset $contentDraftsDataset,
        ConfigResolverInterface $configResolver
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->searchService = $searchService;
        $this->contentDraftsDataset = $contentDraftsDataset;
        $this->configResolver = $configResolver;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PackageDbNotExists) {
            throw new UnexpectedTypeException($constraint, PackageDbNotExists::class);
        }

        if (!$value) {
            return;
        }

        $packageContributorId = $this->configResolver->getParameter('package_contributor_id', 'app');
        $packageListLocationId = $this->configResolver->getParameter('package_list_location_id', 'app');

        $params = [
            'parent_location_id' => $packageListLocationId,
            'target_field' => $constraint->targetField,
            'search' => $value,
        ];

        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUser($packageContributorId)
        );

        $drafts = $this->contentDraftsDataset->load();

        $repositoryMetadata = new RepositoryMetadata($value);
        $repositoryId = $repositoryMetadata->getRepositoryId();

        unset($repositoryMetadata);

        $isDraftExist = in_array($repositoryId, array_column($drafts->getContentDrafts(), 'name'));

        if ($isDraftExist) {
            $this->context
                ->buildViolation($constraint->messageWaitingForApproval)
                ->addViolation();
        }

        $query = $this->getPackageQuery($params);

        if (!$isDraftExist && $this->searchService->findContentInfo($query)->totalCount > 0) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ name }}', $this->getMessageValue($params['target_field']))
                ->addViolation();
        }
    }

    /**
     * @param array $params
     *
     * @return Query
     */
    private function getPackageQuery(array $params): Query
    {
        $query = new Query();

        if (!isset($params['parent_location_id']) ||
            !isset($params['target_field']) ||
            !isset($params['search'])
            ) {
            throw new MissingOptionsException('One of required options are missing: \'parent_location_id\', \'target_field\', \'search\'  ', []);
        }

        $query->query = new LogicalAnd([
            new Query\Criterion\ParentLocationId($params['parent_location_id']),
            new ContentTypeIdentifier('package'),
            new Field($params['target_field'], Operator::EQ, $params['search']),
        ]);

        return $query;
    }

    /**
     * @param string $targetField
     *
     * @return mixed|string
     */
    private function getMessageValue(string $targetField): string
    {
        return array_key_exists($targetField, self::$VALIDATION_MESSAGE) ? self::$VALIDATION_MESSAGE[$targetField] : '';
    }
}
