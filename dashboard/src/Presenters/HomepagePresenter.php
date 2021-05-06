<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Presenters;

use Nette\Application\UI\Presenter;

/**
 * @property HomepageTemplate $template
 */
final class HomepagePresenter extends Presenter
{
    public function renderDefault(): void
    {
        $this->template->jobs = [];
    }
}
