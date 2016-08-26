<?php
/**
 * A controller to deal with overall site stuff
 * @author David Christian Liedle <david.liedle@gmail.com>
 *
 */
namespace AppBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

class SiteController extends Controller
{

    /**
     * This method will look inside of the YAML settings we define in parameters.yml to read the name of the variable we
     * set to store our Content ID for the layout.
     *
     * @param null $template
     * @return mixed
     */
    public function getSettingsAction($template = null)
    {

        $layoutId = $this->container->getParameter('layout_id');

        $layoutContent = $this->getRepository()->getContentService()->loadContent($layoutId);

        return $this->render($template, array('content' => $layoutContent));

    } // End of public function getSettingsAction($template = null)

} // End of class SiteController extends Controller
