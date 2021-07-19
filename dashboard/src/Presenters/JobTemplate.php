<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Presenters;

use Nette\Bridges\ApplicationLatte\Template;
use PHPMate\Worker\Domain\Job\Job;

class JobTemplate extends Template
{
    public string $baseUrl;

    /**
     * @var \stdClass[]
     */
    public array $flashes = [];

    /**
     * @var Job[]
     */
    public array $jobs = [];

    public ?Job $activeJob;
}
