<?php

/**
 * LayoutController.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;

/**
 * Class LayoutController.
 */
class LayoutController extends Controller
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $layoutContent;

    /**
     * Renders given $template with layout settings.
     *
     * @param $template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function showPartAction($template)
    {
        return $this->render($template, [
            'content' => $this->getLayoutContent(),
        ]);
    }

    /**
     * Returns `Layout` Content object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getLayoutContent()
    {
        if (!$this->layoutContent) {
            $this->layoutContent = $this->getRepository()->getContentService()->loadContent(
                $this->getParameter('layout_id')
            );
        }

        return $this->layoutContent;
    }
}
