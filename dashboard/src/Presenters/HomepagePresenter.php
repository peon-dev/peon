<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Presenters;

use Nette\Application\UI\Presenter;


final class HomepagePresenter extends Presenter
{
    public function renderDefault(): void
    {
        $template = $this->createTemplate(HomepageTemplate::class);
        $this->sendTemplate($template);
    }
}
