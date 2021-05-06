<?php

declare(strict_types=1);

namespace PHPMate\Worker\Infrastructure\Job\FileSystem;

use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use PHPMate\Worker\Domain\Job\Job;
use PHPMate\Worker\Domain\Job\JobRepository;

final class FileSystemJobRepository implements JobRepository
{
    public function __construct(
        private string $directory
    ) {}


    public function save(Job $job): void
    {
        $serializedJob = serialize($job);
        $filePath = $this->directory . '/' . $job->getTimestamp();

        FileSystem::write($filePath, $serializedJob);
    }


    /**
     * @return Job[]
     */
    public function findAll(): array
    {
        $files = Finder::findFiles('*')->in($this->directory);

        $jobs = [];

        foreach ($files as $fileInfo) {
            $content = FileSystem::read((string) $fileInfo);
            $jobs[] = unserialize($content, [
                'allowed_classes' => [Job::class]
            ]);
        }

        return $jobs;
    }
}
