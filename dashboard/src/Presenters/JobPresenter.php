<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Presenters;

use PHPMate\Domain\Job\JobId;
use PHPMate\Domain\Job\JobNotFound;
use PHPMate\Domain\Job\JobsCollection;

/**
 * @property JobTemplate $template
 */
final class JobPresenter extends SecuredPresenter
{
    public function __construct(
        private JobsCollection $jobRepository
    ) {
        parent::__construct();
    }


    public function renderDefault(?string $id = null): void
    {
        $this->template->jobs = $this->jobRepository->findAll();

        try {
            $this->template->activeJob = $id ? $this->jobRepository->get(new JobId($id)) : null;
        } catch (JobNotFound) {
            $this->redirect('this', ['id' => null]);
        }
    }
}
