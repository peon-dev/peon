<?php

declare(strict_types=1);

namespace PHPMate\Domain\Notification;

interface Notifier
{
    public function notifyAboutFailedCommand(\LogicException $exception): void;

    public function notifyAboutNewChanges(): void;
}
