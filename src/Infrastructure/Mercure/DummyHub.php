<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Mercure;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;
use Symfony\Component\Mercure\Update;

final class DummyHub implements HubInterface
{
    public function getUrl(): string
    {
        return 'https://internal/.well-known/mercure';
    }


    public function getPublicUrl(): string
    {
        return 'https://external/.well-known/mercure';
    }


    public function getProvider(): TokenProviderInterface
    {
        return new class implements TokenProviderInterface {
            public function getJwt(): string
            {
                return '';
            }
        };
    }


    public function getFactory(): ?TokenFactoryInterface
    {
        return null;
    }


    public function publish(Update $update): string
    {
        return 'id';
    }
}
