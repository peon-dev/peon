<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Notification;

use PHPMate\Domain\Notification\Notifier;

final class DummyNotifier implements Notifier
{
    public function notifyAboutFailedCommand(\RuntimeException $exception): void
    {
    }

    public function notifyAboutNewChanges(): void
    {
    }
}
