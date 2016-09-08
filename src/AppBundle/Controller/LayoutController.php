<?php

namespace AppBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;

class LayoutController extends Controller
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $layoutContent;

    /**
     * Renders given $template with layout settings.
     *
     * @param string $template
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
