<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Notifier;

use PHPMate\Domain\Notification\Notifier;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

final class SymfonyNotifier implements Notifier
{
    public function __construct(
        private NotifierInterface $symfonyNotifier
    ) {}


    public function notifyAboutFailedCommand(\RuntimeException $exception): void
    {
        $notification = (new Notification('Failed during command execution!'))
            ->content($exception->getMessage())
            ->importance(Notification::IMPORTANCE_URGENT);

        $this->symfonyNotifier->send($notification);
    }


    public function notifyAboutNewChanges(): void
    {
        $notification = (new Notification('New changes!'))
            ->importance(Notification::IMPORTANCE_LOW);

        $this->symfonyNotifier->send($notification);
    }
}
