<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Validator\Constraints as PackageAssert;

class PackageForm
{
    /**
     * @Assert\NotNull()
     * @Assert\Url()
     * @PackageAssert\PackagistUrl()
     * @PackageAssert\PackageDbNotExists(targetField="packagist_url")
     */
    private $url;

    /**
     * @Assert\NotNull()
     * @PackageAssert\PackageDbNotExists(targetField="name")
     */
    private $name;

    /**
     * @PackageAssert\PackageCategoryId()
     */
    private $categories;

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array|null
     */
    public function getCategories(): ?array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }
}
