<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel;

final class JobStatus
{
    public const SCHEDULED = 'scheduled';
    public const IN_PROGRESS = 'in progress';
    public const SUCCEEDED = 'succeeded';
    public const FAILED = 'failed';
    public const CANCELED = 'canceled';
}
