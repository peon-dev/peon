<?php

declare(strict_types=1);

namespace Peon\Domain\Application\Value;

final class ApplicationLanguage
{
    public function __construct(
        public readonly string $language,
        public readonly string $version,
    ) {
    }


    public function isSameAs(ApplicationLanguage|null $other): bool
    {
        if ($other === null) {
            return false;
        }

        return $this->language === $other->language && $this->version === $other->version;
    }
}
