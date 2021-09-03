<?php

declare(strict_types=1);

namespace PHPMate\Domain\Process;

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
     * @see https://regex101.com/r/2EhyJG/2
     */
    private function sanitizeProcessResult(ProcessResult $processResult): ProcessResult
    {
        $regex = '/([\w-]*:)(?<password>[\w-]*)(@[\w-]*\.\w*)/m';

        $sanitizedCommand = Strings::replace($processResult->command, $regex, '${1}****${3}');
        $sanitizedOutput = Strings::replace($processResult->output, $regex, '${1}****${3}');

        return new ProcessResult(
            $sanitizedCommand,
            $processResult->exitCode,
            $sanitizedOutput,
            $processResult->duration
        );
    }
}
