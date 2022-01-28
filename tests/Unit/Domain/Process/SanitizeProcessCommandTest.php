<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Process;

use Peon\Domain\Process\ProcessLogger;
use Peon\Domain\Process\Value\ProcessResult;
use PHPUnit\Framework\TestCase;

final class SanitizeProcessCommandTest extends TestCase
{
    /**
     * @return \Generator<array{string, string}>
     */
    public function provideTestProcessOutputWillBeSanitizedData(): \Generator
    {
        yield [
            'git clone https://username:password-password@gitlab.com/peon/peon.git .',
            'git clone https://username:****@gitlab.com/peon/peon.git .',
        ];

        yield [
            'git clone http://username:password-password@gitlab.com/peon/peon.git .',
            'git clone http://username:****@gitlab.com/peon/peon.git .',
        ];

        yield [
            'git clone git://username:password-password@gitlab.com/peon/peon.git .',
            'git clone git://username:****@gitlab.com/peon/peon.git .',
        ];

        yield [
            "git clone https://username:password-password@gitlab.com/peon/peon.git .\ngit clone https://username:password-password@gitlab.com/peon/peon.git .",
            "git clone https://username:****@gitlab.com/peon/peon.git .\ngit clone https://username:****@gitlab.com/peon/peon.git .",
        ];

        yield [
            'git clone https://username@gitlab.com/peon/peon.git .',
            'git clone https://username@gitlab.com/peon/peon.git .',
        ];
    }
}
