<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Job\FileSystem;

use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobNotFound;
use PHPMate\Domain\Job\JobRepository;

final class FileSystemJobRepository implements JobRepository
{
    public function __construct(
        private string $directory
    ) {}


    public function save(Job $job): void
    {
        $serializedJob = base64_encode(serialize($job));
        $filePath = $this->directory . '/' . $job->getTimestamp() . '.dat';

        FileSystem::write($filePath, $serializedJob);
    }


    /**
     * @return Job[]
     */
    public function findAll(): array
    {
        $files = Finder::findFiles('*.dat')->in($this->directory);
        $jobs = [];

        foreach ($files as $fileInfo) {
            $content = FileSystem::read((string) $fileInfo);

            /** @var Job $job */
            $job = unserialize(base64_decode($content), [
                'allowed_classes' => true,
            ]);

            $jobs[$job->getTimestamp()] = $job;
        }

        krsort($jobs);

        return $jobs;
    }


    public function get(int $id): Job
    {
        $jobs = $this->findAll();

        if (!isset($jobs[$id])) {
            throw new JobNotFound();
        }

        return $jobs[$id];
    }
}
