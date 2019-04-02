<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Event\Subscriber;

use AppBundle\Event\AddPackageEvent;
use eZ\Publish\API\Repository\Values\Content\Content;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class NotificationSenderSubscriber.
 */
class NotificationSenderSubscriber implements EventSubscriberInterface
{
    /** @var \Swift_Mailer */
    private $mailer;

    /** @var \Twig_Environment */
    private $twig;

    /** @var array */
    private $recipients = [];

    public function __construct(
        \Swift_Mailer $mailer,
        \Twig_Environment $twig,
        array $recipients
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->recipients = $recipients;
    }

    /** {@inheritdoc} */
    public static function getSubscribedEvents(): array
    {
        return [
            AddPackageEvent::EVENT_NAME => 'onPackageCreate',
        ];
    }

    /**
     * @param AddPackageEvent $event
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onPackageCreate(AddPackageEvent $event)
    {
        $message = $this->prepareMailMessageAfterCreatePackage($event->getContent());

        $this->mailer->send($message);
    }

    /**
     * @param Content $content
     *
     * @return \Swift_Message
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function prepareMailMessageAfterCreatePackage(Content $content): \Swift_Message
    {
        $template = $this->twig->loadTemplate('mail/package_add.html.twig');

        $message = new \Swift_Message();
        $message->setFrom($template->renderBlock('from', []));
        $message->setSubject($template->renderBlock('subject', ['name' => $content->getFieldValue('name')]));
        $message->setTo($this->recipients);
        $message->setBody($template->renderBlock('body', [
            'name' => $content->getFieldValue('name'),
            'packagist_url' => $content->getFieldValue('packagist_url'),
        ]), 'text/html');

        return $message;
    }
}
