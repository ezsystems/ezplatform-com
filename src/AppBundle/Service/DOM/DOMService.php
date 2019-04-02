<?php

/**
 * DOMService.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\DOM;

use AppBundle\Url\UrlBuilder;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class DOMService.
 */
class DOMService implements DOMServiceInterface
{
    const ABSOLUTE_URL_FILTER_ELEMENTS = ['a', 'img'];

    /** @var \AppBundle\Url\UrlBuilder */
    private $urlBuilder;

    public function __construct(UrlBuilder $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     * @param array $elements
     *
     * @return Crawler
     */
    public function removeElementsFromDOM(Crawler $crawler, array $elements): Crawler
    {
        foreach ($elements as $element) {
            $this->removeElementFromDOM($crawler, $element);
        }

        return $crawler;
    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     * @param array $urlAttributes
     *
     * @return Crawler
     */
    public function setAbsoluteURL(Crawler $crawler, array $urlAttributes): Crawler
    {
        foreach (self::ABSOLUTE_URL_FILTER_ELEMENTS as $element) {
            $this->setAbsoluteURLForGivenElement($element, $crawler, $urlAttributes);
        }

        return $crawler;
    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     * @param string $element
     *
     * @return array
     */
    private function removeElementFromDOM(Crawler $crawler, string $element): array
    {
        return $crawler
            ->filter($element)
            ->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    if ($node->nodeName === 'img') {
                        $parent = $node->parentNode->parentNode;
                        $elementToRemove = $node->parentNode;
                        $parent->removeChild($elementToRemove);
                    } else {
                        $node->parentNode->removeChild($node);
                    }
                }
            });
    }

    /**
     * @param string $element
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     * @param array $urlAttributes
     *
     * @return array
     */
    private function setAbsoluteURLForGivenElement(string $element, Crawler $crawler, array $urlAttributes): array
    {
        return $crawler
            ->filter($element)
            ->each(function (Crawler $crawler) use ($urlAttributes) {
                foreach ($crawler as $node) {
                    $attributes[] = isset($urlAttributes['repository']) ? $urlAttributes['repository'] : null;
                    $attr = $this->getAttributeType($node);
                    $link = $node->getAttribute($attr);

                    if ($link && false === strpos($link, 'http')) {
                        $attributes[] = isset($urlAttributes['link'][$attr]) ? $urlAttributes['link'][$attr] : null;
                        $attributes[] = $link;
                        $this->setLinkAttribute($node, $attr, $attributes);
                    }
                }
            });
    }

    /**
     * @param \DOMElement $element
     * @param string $attr
     * @param array $urlAttributes
     *
     * @return \DOMAttr
     */
    private function setLinkAttribute(\DOMElement $element, string $attr, array $urlAttributes): \DOMAttr
    {
        return $element->setAttribute($attr, call_user_func_array([$this->urlBuilder, 'urlGlue'], $urlAttributes));
    }

    /**
     * @param \DOMElement $element
     *
     * @return string
     */
    private function getAttributeType(\DOMElement $element): string
    {
        $attr = '';

        if ($element->nodeName === 'img') {
            $attr = 'src';
        } elseif ($element->nodeName === 'a') {
            $attr = 'href';
        }

        return $attr;
    }
}
