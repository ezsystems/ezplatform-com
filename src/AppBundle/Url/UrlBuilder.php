<?php

/**
 * UrlBuilder.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Url;

/**
 * Class UrlBuilder.
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
            $argument = !empty($argument) && is_string($argument)
                ? trim($argument, '/')
                : null;

            $absoluteUrl .= $argument;

            if (next($arguments)) {
                $absoluteUrl .= '/';
            }
        }

        return $absoluteUrl;
    }
}
