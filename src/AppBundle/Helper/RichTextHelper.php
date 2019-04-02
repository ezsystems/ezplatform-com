<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Helper;

/**
 * Class RichTextHelper.
 */
class RichTextHelper
{
    /**
     * @param $stringToXml
     *
     * @return string
     */
    public function getXmlString($stringToXml): string
    {
        $escapedString = htmlspecialchars($stringToXml, ENT_XML1);

        return <<< EOX
<?xml version='1.0' encoding='utf-8'?>
<section 
    xmlns="http://docbook.org/ns/docbook" 
    xmlns:xlink="http://www.w3.org/1999/xlink" 
    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" 
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" 
    version="5.0-variant ezpublish-1.0">
<para>{$escapedString}</para>
</section>
EOX;
    }
}
