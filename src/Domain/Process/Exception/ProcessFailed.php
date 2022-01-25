<?php

declare(strict_types=1);

namespace Peon\Domain\Process\Exception;

use Peon\Domain\Process\Value\ProcessResult;

final class ProcessFailed extends \RuntimeException
{
    public ProcessResult $result;
}
