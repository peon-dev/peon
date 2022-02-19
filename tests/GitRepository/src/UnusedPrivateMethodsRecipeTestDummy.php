<?php

declare(strict_types=1);

namespace PeonDogFood;

final class UnusedPrivateMethodsRecipeTestDummy
{
    public function publicMethodShouldNotBeDeleted(): void
    {
        $this->weNeedThisMethod();
    }

    private function weNeedThisMethod(): void
    {}

    private function weDoNotNeedThisMethod(): void
    {}
}
