<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Presenters;

use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;
use PHPMate\Dashboard\Security\BasicAuthChecker;

abstract class SecuredPresenter extends Presenter
{
    protected BasicAuthChecker $basicAuthChecker;


    public function injectBasicAuthChecker(BasicAuthChecker $basicAuthChecker): void
    {
        $this->basicAuthChecker = $basicAuthChecker;
    }


    /**
     * @param \ReflectionMethod $element
     * @throws ForbiddenRequestException
     */
    public function checkRequirements($element): void
    {
        parent::checkRequirements($element);

        $this->basicAuthChecker->check();
    }
}
