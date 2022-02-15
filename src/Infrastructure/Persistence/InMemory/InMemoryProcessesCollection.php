<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\InMemory;

use Peon\Domain\Process\Process;
use Peon\Domain\Process\ProcessesCollection;
use Peon\Domain\Process\Value\ProcessId;

final class InMemoryProcessesCollection implements ProcessesCollection
{
    /**
     * @var array<string, Process>
     */
    private array $processes = [];


    public function nextIdentity(): ProcessId
    {
        return new ProcessId((string) count($this->processes));
    }


    public function save(Process $process): void
    {
        $this->processes[$process->processId->id] = $process;
    }
}
