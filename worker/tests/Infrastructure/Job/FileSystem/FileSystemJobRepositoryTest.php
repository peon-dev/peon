<?php
declare(strict_types=1);

namespace PHPMate\Worker\Tests\Infrastructure\Job\FileSystem;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use PHPMate\Worker\Domain\Job\Job;
use PHPMate\Worker\Infrastructure\Job\FileSystem\FileSystemJobRepository;
use PHPUnit\Framework\TestCase;

class FileSystemJobRepositoryTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = __DIR__ . '/../../../../var/tmp/' . Random::generate();
        FileSystem::createDir($this->directory);
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        FileSystem::delete($this->directory);
    }


    public function test(): void
    {
        $repository = new FileSystemJobRepository($this->directory);

        self::assertCount(0, $repository->findAll());

        $repository->save(new Job(1));

        self::assertCount(1, $repository->findAll());
    }
}
