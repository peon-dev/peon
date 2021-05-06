<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Presenters;

use Nette\Bridges\ApplicationLatte\Template;
use PHPMate\Worker\Domain\Job\Job;

class HomepageTemplate extends Template
{
    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @var \stdClass[]
     */
    public $flashes = [];

    /**
     * @var Job[]
     */
    public array $jobs = [];

    public Job $activeJob;
}
