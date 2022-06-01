<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Config\SecurityConfig;

return static function (ContainerConfigurator $containerConfigurator, SecurityConfig $securityConfig): void {
    $securityConfig->enableAuthenticatorManager(true);

    $securityConfig->passwordHasher(PasswordAuthenticatedUserInterface::class, 'auto');

    $securityConfig->provider('users_in_memory')
        ->memory([]);

    $securityConfig->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);

    $securityConfig->firewall('main')
        ->lazy(true)
        ->provider('users_in_memory');
};
