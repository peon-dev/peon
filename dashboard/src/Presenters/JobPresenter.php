<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Presenters;

use PHPMate\Worker\Domain\Job\JobRepository;

/**
 * @property JobTemplate $template
 */
final class JobPresenter extends SecuredPresenter
{
    public function __construct(
        private JobRepository $jobRepository
    ) {
        parent::__construct();
    }


    public function renderDefault(?int $id = null): void
    {
        $this->template->jobs = $this->jobRepository->findAll();
        $this->template->activeJob = $id ? $this->jobRepository->get($id) : null;
    }
}
