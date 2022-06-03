<?php

declare(strict_types=1);

use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineBuildConfigurationType;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineCronExpressionType;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineEnabledRecipeType;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineJobIdType;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineMergeRequestType;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineProcessIdType;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineProjectIdType;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineEnabledRecipesArrayType;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineRecipeNameType;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineTaskIdType;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineUserIdType;
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
                DoctrineRecipeNameType::NAME => DoctrineRecipeNameType::class,
                DoctrineEnabledRecipeType::NAME => DoctrineEnabledRecipeType::class,
                DoctrineBuildConfigurationType::NAME => DoctrineBuildConfigurationType::class,
                DoctrineMergeRequestType::NAME => DoctrineMergeRequestType::class,
                DoctrineProcessIdType::NAME => DoctrineProcessIdType::class,
                DoctrineUserIdType::NAME => DoctrineUserIdType::class,
            ],
        ],
        'orm' => [
            'auto_generate_proxy_classes' => true,
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
            'auto_mapping' => true,
            'mappings' => [
                'Peon' => [
                    'is_bundle' => false,
                    'type' => 'xml',
                    'dir' => '%kernel.project_dir%/src/Infrastructure/Persistence/Doctrine/Mapping',
                    'prefix' => 'Peon\Domain',
                ],
            ],
        ],
    ]);
};
