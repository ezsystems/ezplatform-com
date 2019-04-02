<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Twig helper for extract video id from youtube url.
 *
 * Class YoutubeIdExtractorExtensio
 */
class YoutubeIdExtractorExtension extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app.youtube_extract_id';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('app_youtube_extract_id', [$this, 'extractId']),
        ];
    }

    /**
     * Returns youtube video id.
     *
     * @param string $string
     *
     * @return string|null
     */
    public function extractId(string $string): ?string
    {
        $regexp = '/(?:https?:)?(?:\/\/)?(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\S*?[^\w\s-])'
                . '(?P<id>[\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>|<\/a>))[?=&+%\w.-]*/i';

        preg_match($regexp, $string, $matches);

        return $matches['id'] ?? null;
    }
}
