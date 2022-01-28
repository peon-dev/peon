<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

final class SanitizeProcessCommand
{
    public function maskCredentials(string $command): string
    {
        return $command;
    }

    /**
     *
     * @see https://regex101.com/r/2EhyJG/2
    private function sanitizeProcessResult(ProcessResult $processResult): ProcessResult
    {
        $regex = '/([\w-]*:)(?<password>[\w-]*)(@[\w-]*\.\w*)/m';

        $sanitizedCommand = Strings::replace($processResult->command, $regex, '${1}****${3}');
        $sanitizedOutput = Strings::replace($processResult->output, $regex, '${1}****${3}');
     */
}
