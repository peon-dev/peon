<?php

declare(strict_types=1);

use PHPMate\Infrastructure\Persistence\Doctrine\Type\DoctrineCronExpressionType;
use PHPMate\Infrastructure\Persistence\Doctrine\Type\DoctrineJobIdType;
use PHPMate\Infrastructure\Persistence\Doctrine\Type\DoctrineProjectIdType;
use PHPMate\Infrastructure\Persistence\Doctrine\Type\DoctrineEnabledRecipesArrayType;
use PHPMate\Infrastructure\Persistence\Doctrine\Type\DoctrineTaskIdType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'url' => '%env(resolve:DATABASE_URL)%',
            'types' => [
                DoctrineProjectIdType::NAME => DoctrineProjectIdType::class,
                DoctrineTaskIdType::NAME => DoctrineTaskIdType::class,
                DoctrineJobIdType::NAME => DoctrineJobIdType::class,
                DoctrineCronExpressionType::NAME => DoctrineCronExpressionType::class,
                DoctrineEnabledRecipesArrayType::NAME => DoctrineEnabledRecipesArrayType::class,
            ],
        ],
        'orm' => [
            'auto_generate_proxy_classes' => true,
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
            'auto_mapping' => true,
            'mappings' => [
                'PHPMate' => [
                    'is_bundle' => false,
                    'type' => 'xml',
                    'dir' => '%kernel.project_dir%/src/Infrastructure/Persistence/Doctrine/Mapping',
                    'prefix' => 'PHPMate\Domain',
                ],
            ],
        ],
    ]);
};
