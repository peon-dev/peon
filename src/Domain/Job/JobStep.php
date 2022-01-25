<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

enum JobStep: string
{
    case PREPARE = 'prepare';
    case BUILD = 'build';
    case PROCESS = 'process';
    case MERGE_REQUEST = 'merge_request';
    case CLEANUP = 'cleanup';
}
