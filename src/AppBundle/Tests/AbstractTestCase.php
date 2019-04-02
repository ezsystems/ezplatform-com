<?php

/**
 * AbstractTestCase.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class AbstractTestCase.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @param \ReflectionClass $class
     * @param string $methodName
     *
     * @return \ReflectionMethod
     *
     * @throws \ReflectionException
     */
    protected function getInaccessibleClassMethod(\ReflectionClass $class, string $methodName): \ReflectionMethod
    {
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}
