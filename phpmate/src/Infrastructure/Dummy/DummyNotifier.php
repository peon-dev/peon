<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Dummy;

use PHPMate\Domain\Notification\Notifier;

final class DummyNotifier implements Notifier
{
    public function notifyAboutFailedCommand(\LogicException $exception): void
    {
    }

    public function notifyAboutNewChanges(): void
    {
    }
}
