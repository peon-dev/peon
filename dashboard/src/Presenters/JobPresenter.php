<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Presenters;

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


    public function renderDefault(?int $id = null): void
    {
        $this->template->jobs = $this->jobRepository->findAll();

        try {
            $this->template->activeJob = $id ? $this->jobRepository->get($id) : null;
        } catch (JobNotFound) {
            $this->redirect('this', ['id' => null]);
        }
    }
}
