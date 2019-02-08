<?php

/**
 * RepositoryMetadataTest
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Tests\ValueObject;

use AppBundle\Tests\AbstractTestCase;
use AppBundle\Tests\Fixtures\PackageTestFixture;
use AppBundle\ValueObject\RepositoryMetadata;

/**
 * Test case for RepositoryMetadata class
 *
 * Class RepositoryMetadataTest
 *
 * @package AppBundle\Tests\ValueObject
 */
class RepositoryMetadataTest extends AbstractTestCase
{
    /**
     * @var \AppBundle\ValueObject\Package
     */
    protected $package;

    /**
     * @var \AppBundle\ValueObject\RepositoryMetadata
     */
    protected $repositoryMetadata;

    /**
     * @var \ReflectionMethod
     */
    protected $methodGetUsernameFromRepositoryUrl;

    /**
     * @var \ReflectionMethod
     */
    protected $methodGetRepositoryNameFromRepositoryUrl;

    /**
     * @var string
     */
    protected $repositoryName = '';

    /**
     * @var string
     */
    protected $repositoryUsername = '';

    /**
     * @throws \ReflectionException
     */
    protected function setUp()
    {
        $fixture = new PackageTestFixture();
        $this->package = $fixture->getPackage();

        $this->repositoryName = 'package';
        $this->repositoryUsername = 'test';

        $repositoryMetadataReflection = new \ReflectionClass(RepositoryMetadata::class);
        $this->methodGetUsernameFromRepositoryUrl = $this->getInaccessibleClassMethod($repositoryMetadataReflection, 'getUsernameFromRepositoryUrl');
        $this->methodGetRepositoryNameFromRepositoryUrl = $this->getInaccessibleClassMethod($repositoryMetadataReflection, 'getRepositoryNameFromRepositoryUrl');

        $this->repositoryMetadata = new RepositoryMetadata($this->package->repository);
    }

    /**
     * Tests instantiation of RepositoryMetadata
     */
    public function testCreateRepositoryMetadataObjectBasedOnRepositoryUrl()
    {
        $this->assertInstanceOf(RepositoryMetadata::class, $this->repositoryMetadata);
    }

    /**
     * Tests if repository name is parsed properly from repositoryId
     *
     * @covers \AppBundle\ValueObject\RepositoryMetadata::getRepositoryNameFromRepositoryUrl()
     */
    public function testReturnRepositoryNameFromRepositoryId()
    {
        $this->assertEquals(
            $this->repositoryName,
            $this->methodGetRepositoryNameFromRepositoryUrl->invokeArgs($this->repositoryMetadata, [])
        );
    }

    /**
     * Tests if repository username is parsed properly from repositoryId
     *
     * @covers \AppBundle\ValueObject\RepositoryMetadata::getUsernameFromRepositoryUrl()
     */
    public function testReturnRepositoryUsernameFromRepositoryId()
    {
        $this->assertEquals(
            $this->repositoryUsername,
            $this->methodGetUsernameFromRepositoryUrl->invokeArgs($this->repositoryMetadata, [])
        );
    }

    /**
     * Tests if property repositoryName is returned
     *
     * @covers \AppBundle\ValueObject\RepositoryMetadata::getRepositoryName()
     */
    public function testRepositoryMetadataObjectShouldHasPropertyRepositoryName()
    {
        $this->assertEquals(
            $this->repositoryName,
            $this->repositoryMetadata->getRepositoryName()
        );
    }

    /**
     * Tests if property username
     *
     * @covers \AppBundle\ValueObject\RepositoryMetadata::getUsername()
     */
    public function testRepositoryMetadataObjectShouldHasPropertyRepositoryUsername()
    {
        $this->assertEquals(
            $this->repositoryUsername,
            $this->repositoryMetadata->getUsername()
        );
    }
}
