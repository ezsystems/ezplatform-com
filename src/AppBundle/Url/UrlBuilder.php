<?php

/**
 * UrlBuilder
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Url;

/**
 * Class UrlBuilder
 *
 * @package AppBundle\Url
 */
class UrlBuilder
{
    /**
     * @param mixed ...$arguments
     *
     * @return string
     */
    public function urlGlue(...$arguments): string
    {
        $absoluteUrl = '';

        foreach ($arguments as $argument) {
            $argument = trim($argument, '/');

            $absoluteUrl .= $argument;

            if (next($arguments)) {
                $absoluteUrl .= '/';
            }
        }

        return $absoluteUrl;
    }
}
