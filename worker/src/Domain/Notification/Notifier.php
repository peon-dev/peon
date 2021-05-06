<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Notification;

interface Notifier
{
    public function notifyAboutFailedCommand(\LogicException $exception): void;

    // TODO: link to merge request
    public function notifyAboutNewChanges(): void;
}
