<?php

declare(strict_types=1);

namespace Peon\Domain\User;

interface HashPlainTextPassword
{
    public function hash(string $plainTextPassword): string;
}
