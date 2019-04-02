<?php

/**
 * DOMServiceInterface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\DOM;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Interface DOMServiceInterface.
 */
interface DOMServiceInterface
{
    public function removeElementsFromDOM(Crawler $crawler, array $elements): Crawler;

    public function setAbsoluteURL(Crawler $crawler, array $urlAttributes): Crawler;
}
