<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Event;

use eZ\Publish\API\Repository\Values\Content\Content;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AddPackageEvent.
 */
class AddPackageEvent extends Event
{
    const EVENT_NAME = 'on.package.create';

    /** @var \eZ\Publish\Core\Repository\Values\Content\Content */
    private $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    /** @return \eZ\Publish\Core\Repository\Values\Content\Content */
    public function getContent(): Content
    {
        return $this->content;
    }
}
