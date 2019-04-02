<?php

/**
 * UrlBuilderTest.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Url;

use AppBundle\Url\UrlBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Test case for UrlBuilderTest.
 *
 * Class UrlBuilderTest
 */
class UrlBuilderTest extends TestCase
{
    /** @var \AppBundle\Url\UrlBuilder */
    protected $urlBuilder;

    /** @var string */
    protected $absoluteUrl = '';

    protected function setUp()
    {
        $this->urlBuilder = new UrlBuilder();
        $this->absoluteUrl = 'https://www.ezplatform.com/url-test';
    }

    /** Tests instantiation of UrlBuilder */
    public function testCreateUrlBuilderInstance()
    {
        $this->assertInstanceOf(UrlBuilder::class, $this->urlBuilder);
    }

    /**
     * @param $actualResult
     *
     * @dataProvider urlAttributesProvider()
     */
    public function testReturnAbsoluteUrl($actualResult)
    {
        $this->assertEquals(
            $this->absoluteUrl,
            call_user_func_array([$this->urlBuilder, 'urlGlue'], $actualResult)
        );
    }

    /** @return iterable */
    public function urlAttributesProvider(): iterable
    {
        return [
            [['https://www.ezplatform.com/', 'url-test']],
            [['https://www.ezplatform.com', '/url-test']],
            [['https://www.ezplatform.com', 'url-test']],
            [['https://www.ezplatform.com/', '/url-test']],
            [['/https://www.ezplatform.com/', 'url-test']],
            [['/https://www.ezplatform.com/', 'url-test/']],
            [['/https://www.ezplatform.com/', '/url-test']],
            [['/https://www.ezplatform.com/', '/url-test/']],
            [['/https://www.ezplatform.com', '/url-test']],
            [['/https://www.ezplatform.com', 'url-test/']],
            [['/https://www.ezplatform.com', '/url-test/']],
        ];
    }
}
