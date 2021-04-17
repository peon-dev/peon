<?php

declare(strict_types=1);

namespace PHPMate\Domain\Notification;

interface Notifier
{
    public function notifyFailedCommand(\LogicException $exception): void;
}
