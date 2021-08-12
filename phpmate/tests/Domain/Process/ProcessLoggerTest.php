<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Process;

use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Process\ProcessResult;
use PHPUnit\Framework\TestCase;

final class ProcessLoggerTest extends TestCase
{
    /**
     * @dataProvider provideTestProcessOutputWillBeSanitizedData
     */
    public function testProcessOutputWillBeSanitized(string $sensitiveInfo, string $expectedSanitization): void
    {
        $processResult = new ProcessResult($sensitiveInfo, 0, $sensitiveInfo, 0);

        $logger = new ProcessLogger();
        $logger->logResult($processResult);

        $logs = $logger->getLogs();

        $loggedResult = $logs[array_key_first($logs)];

        self::assertSame($expectedSanitization, $loggedResult->command);
        self::assertSame($expectedSanitization, $loggedResult->output);
    }


    /**
     * @return \Generator<array{string, string}>
     */
    public function provideTestProcessOutputWillBeSanitizedData(): \Generator
    {
        yield [
            'git clone https://username:password-password@gitlab.com/phpmate/phpmate.git .',
            'git clone https://username:****@gitlab.com/phpmate/phpmate.git .',
        ];

        yield [
            'git clone http://username:password-password@gitlab.com/phpmate/phpmate.git .',
            'git clone http://username:****@gitlab.com/phpmate/phpmate.git .',
        ];

        yield [
            'git clone git://username:password-password@gitlab.com/phpmate/phpmate.git .',
            'git clone git://username:****@gitlab.com/phpmate/phpmate.git .',
        ];

        yield [
            "git clone https://username:password-password@gitlab.com/phpmate/phpmate.git .\ngit clone https://username:password-password@gitlab.com/phpmate/phpmate.git .",
            "git clone https://username:****@gitlab.com/phpmate/phpmate.git .\ngit clone https://username:****@gitlab.com/phpmate/phpmate.git .",
        ];

        yield [
            'git clone https://username@gitlab.com/phpmate/phpmate.git .',
            'git clone https://username@gitlab.com/phpmate/phpmate.git .',
        ];
    }
}
