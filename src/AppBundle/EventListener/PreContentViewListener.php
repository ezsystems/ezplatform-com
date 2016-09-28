<?php

namespace AppBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;

/**
 * Event listener used for injecting parameters in content views.
 */
class PreContentViewListener
{
    /** @var array */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Injects additional parameters info content view objects.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent $event
     */
    public function onPreContentView(PreContentViewEvent $event)
    {
        $contentView = $event->getContentView();
        $contentView->addParameters($this->options);
    }
}
