<?php

declare(strict_types=1);

use Peon\Domain\User\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $securityConfig): void {
    $securityConfig->enableAuthenticatorManager(true);

    $securityConfig->passwordHasher(PasswordAuthenticatedUserInterface::class, 'auto');

    $securityConfig->provider('doctrine_user_provider')
        ->entity()
            ->class(User::class)
            ->property('username');

    $securityConfig->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js|build)/')
        ->security(false);

    $mainFirewall = $securityConfig->firewall('main');
    $mainFirewall
        ->lazy(true)
        ->provider('doctrine_user_provider')
        ->formLogin()
            ->loginPath('login')
            ->checkPath('login')
            ->enableCsrf(true);
    $mainFirewall
        ->logout()
            ->path('logout');


    $securityConfig->accessControl()
        ->path('^/login$')
        ->roles('PUBLIC_ACCESS');

    $securityConfig->accessControl()
        ->roles('ROLE_USER');
};
