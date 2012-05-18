<?php
/**
 * File containing the LegacyKernelController class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use eZ\CherryMvc\Controller;
use Symfony\Component\HttpFoundation\Response;
use \ezpKernel;

class LegacyKernelController extends Controller
{
    public function indexAction()
    {
        $legacyRoot = $this->container->getParameter( 'ezpublish_legacy.root_dir' );
        require_once $legacyRoot . "/autoload.php";

        chdir( $legacyRoot );
        $kernel = new ezpKernel;
        $result = $kernel->run();

        $kernel->shutdown();

        return new Response(
            $result["content"]
        );
    }
}