<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Presenters;

use Nette\Application\UI\Presenter;
use PHPMate\Worker\Domain\Job\JobRepository;

/**
 * @property HomepageTemplate $template
 */
final class HomepagePresenter extends Presenter
{
    public function __construct(
        private JobRepository $jobRepository
    ) {
        parent::__construct();
    }


    public function renderDefault(): void
    {
        $this->template->jobs = $this->jobRepository->findAll();
    }


    public function renderDetail(int $id): void
    {
        $this->template->jobs = $this->jobRepository->findAll();
        $this->template->activeJob = $this->jobRepository->get($id);
    }
}
