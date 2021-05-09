<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Process;

use Nette\Utils\Strings;

final class ProcessLogger
{
    /**
     * @var ProcessResult[]
     */
    private array $logs = [];


    public function logResult(ProcessResult $processResult): void
    {
        $sanitizedProcessResult = $this->sanitizeProcessResult($processResult);

        $this->logs[] = $sanitizedProcessResult;
    }

    /**
     * @return ProcessResult[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }


    /**
     * @see https://regex101.com/r/AQDD6L/1
     */
    private function sanitizeProcessResult(ProcessResult $processResult): ProcessResult
    {
        $regex = '/[\w-]*:(?<password>[\w-]*)@[\w-]*\.\w*/m';
        $sanitizedOutput = Strings::replace($processResult->output, $regex, '$0****$2');

        return new ProcessResult(
            $processResult->command,
            $processResult->exitCode,
            $sanitizedOutput
        );
    }
}
