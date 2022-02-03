<?php

declare(strict_types=1);

namespace Peon\Domain\Process\Exception;

use Peon\Domain\Process\Value\ProcessResult;
use Throwable;

final class ProcessFailed extends \RuntimeException
{
    public function __construct(
        public readonly ProcessResult $result,
        Throwable|null $previous = null,

    ) {
        $message = $previous ? $previous->getMessage() : $this->result->output;

        parent::__construct($message, previous: $previous);
    }
}
