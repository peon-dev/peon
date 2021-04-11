<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Rector;

use PHPMate\Domain\Rector\Rector;
use PHPMate\Domain\Rector\RectorBinary;
use PHPUnit\Framework\TestCase;

class RectorTest extends TestCase
{
    public function testProcess(): void
    {
        $projectDirectory = '/';

        $rectorBinary = $this->createMock(RectorBinary::class);
        $rectorBinary->expects(self::once())
            ->method('executeCommand')
            ->with(
                $projectDirectory,
                'process'
            );

        $rector = new Rector($rectorBinary);
        $rector->process($projectDirectory);
    }
}
