<?php

namespace AppBundle\Service\Packagist\Test;

use AppBundle\Service\Packagist\Mapper;
use AppBundle\Service\Packagist\Package;
use Packagist\Api\Result\Package as ApiPackage;
use Packagist\Api\Result\Package\Author;
use Packagist\Api\Result\Package\Downloads;
use Packagist\Api\Result\Package\Maintainer;
use Packagist\Api\Result\Package\Version;
use PHPUnit\Framework\TestCase;

/**
 * Test case for mapper.
 */
class MapperTest extends TestCase
{
    /**
     * @var \Packagist\Api\Result\Package
     */
    protected $apiPackage;

    /**
     * @var \AppBundle\Service\Packagist\Package
     */
    protected $package;

    /**
     * @var \AppBundle\Service\Packagist\Mapper
     */
    protected $mapper;

    /**
     * Tests mapper without excluded maintainers
     * @covers \AppBundle\Service\Packagist\Mapper::createPackageFromPackagistApiResult()
     */
    public function testCreatePackageFromPackagistApiResultWithoutExcludedMaintainer()
    {
        $mapper = new Mapper([]);
        $package = $this->createPackage();
        $apiPackage = $this->createPackagistApiPackage();
        $this->assertEquals(
            $package,
            $mapper->createPackageFromPackagistApiResult($apiPackage)
        );
    }

    /**
     * Tests mapper with excluded maintainers
     * @covers \AppBundle\Service\Packagist\Mapper::createPackageFromPackagistApiResult()
     */
    public function testCreatePackageFromPackagistApiResultWithExcludedMaintainer()
    {
        $mapper = new Mapper(['ezrobot']);
        $package = $this->createPackage();
        $apiPackage = $this->createPackagistApiPackage();
        $this->assertEquals(
            $package,
            $mapper->createPackageFromPackagistApiResult($apiPackage)
        );
    }

    /**
     * Returns a \Packagist\Api\Result\Package fixture.
     *
     * @return \Packagist\Api\Result\Package
     */
    private function createPackagistApiPackage()
    {
        $data = [
            'name' => 'test/package',
            'description' => 'Test description',
            'time' => '',
            'maintainers' => $this->getMaintainers(),
            'versions' => [
                $this->getVersion(),
            ],
            'type' => '',
            'repository' => 'http://github.com/test/package',
            'downloads' => $this->getDownloads(),
            'favers' => '',
            'abandoned' => false,
            'suggesters' => 0,
            'dependents' => 0,
            'githubStars' => 12,
            'githubForks' => 3,
        ];

        $package = new ApiPackage();
        $package->fromArray($data);

        return $package;
    }

    /**
     * Returns a \AppBundle\Service\Packagist\Package fixture.
     *
     * @return \AppBundle\Service\Packagist\Package
     */
    private function createPackage()
    {
        $package = new Package();

        $package->packageId = 'test/package';
        $package->description = 'Test description';
        $package->downloads = 222;
        $package->maintainers = $this->getMaintainers();
        $package->authorAvatarUrl = Mapper::GITHUB_AVATAR_BASE_URL.'test';
        $package->forks = 3;
        $package->stars = 12;
        $package->author = $this->getAuthor();
        $package->updateDate = \DateTime::createFromFormat(\DateTime::ISO8601, '2017-11-03T19:51:03+00:00');
        $package->checksum = '51483fa78c050b1f68dd9718531bfda8';

        return $package;
    }

    /**
     * @param bool $exclude
     * @return \Packagist\Api\Result\Package\Maintainer[]
     */
    private function getMaintainers($exclude = true)
    {
        $maintainer = new Maintainer();
        $maintainer->fromArray([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'homepage' => 'example.com',
        ]);

        if (!$exclude) {
            $excludedMaintainer = new Maintainer();
            $excludedMaintainer->fromArray([
                'name' => 'ezrobot',
                'email' => 'nospam@ez.no',
                'homepage' => 'ez.no',
            ]);
        }

        $maintainers[] = $maintainer;

        if (!$exclude) {
            $maintainers[] = $excludedMaintainer;
        }

        return $maintainers;
    }

    /**
     * @return \Packagist\Api\Result\Package\Author
     */
    private function getAuthor()
    {
        $author = new Author();
        $author->fromArray([
            'role' => 'author',
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'homepage' => 'example.com',
        ]);

        return $author;
    }

    /**
     * @return \Packagist\Api\Result\Package\Version
     */
    private function getVersion()
    {
        $version = new Version();
        $version->fromArray([
            'name' => 'dev-master',
            'time' => '2017-11-03T19:51:03+00:00',
            'authors' => [
                $this->getAuthor(),
            ],
        ]);

        return $version;
    }

    /**
     * @return \Packagist\Api\Result\Package\Downloads
     */
    private function getDownloads()
    {
        $downloads = new Downloads();
        $downloads->fromArray([
            'total' => 222,
        ]);

        return $downloads;
    }
}
