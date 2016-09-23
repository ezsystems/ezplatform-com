<?php

namespace AppBundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;
use eZ\Publish\API\Repository\ContentTypeService;

class FieldOptionsTwigExtension extends Twig_Extension
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /**
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Returns extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'ez_field_options';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array
     */
    public function getFunctions()
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
     * @param string $contentTypeIdentifier
     * @param string $fieldName
     *
     * @return array
     */
    public function showFieldOptions($contentTypeIdentifier, $fieldName)
    {
        return $this->contentTypeService
            ->loadContentTypeByIdentifier($contentTypeIdentifier)
            ->getFieldDefinition($fieldName)
            ->fieldSettings['options'];
    }
}
