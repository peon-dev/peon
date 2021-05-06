<?php

declare(strict_types=1);

namespace PHPMate\Worker\Infrastructure\Dummy;

use PHPMate\Worker\Domain\Notification\Notifier;

final class DummyNotifier implements Notifier
{
    public function notifyAboutFailedCommand(\LogicException $exception): void
    {
    }

    public function notifyAboutNewChanges(): void
    {
    }
}
