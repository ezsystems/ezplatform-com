<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;
use eZ\Publish\API\Repository\ContentTypeService;

/**
 * Class FieldOptionsTwigExtension.
 */
class FieldOptionsTwigExtension extends Twig_Extension
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService */
    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ez_field_options';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction('ez_field_options', [$this, 'showFieldOptions'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * Returns array of selection fieldType options.
     *
     * @param $contentTypeIdentifier
     * @param $fieldName
     *
     * @return mixed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function showFieldOptions($contentTypeIdentifier, $fieldName)
    {
        return $this->contentTypeService
            ->loadContentTypeByIdentifier($contentTypeIdentifier)
            ->getFieldDefinition($fieldName)
            ->fieldSettings['options'];
    }
}
