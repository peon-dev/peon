<?php

declare(strict_types=1);

namespace Peon\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211229224447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job ADD enabled_recipe JSON DEFAULT NULL');
        $this->addSql("UPDATE job SET enabled_recipe = json_build_object('recipe_name', job.recipe_name, 'baseline_hash', null) WHERE recipe_name IS NOT NULL");
        $this->addSql('ALTER TABLE job DROP recipe_name');
        $this->addSql('COMMENT ON COLUMN job.enabled_recipe IS \'(DC2Type:enabled_recipe)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE job ADD recipe_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE job DROP enabled_recipe');
        $this->addSql('COMMENT ON COLUMN job.recipe_name IS \'(DC2Type:recipe_name)\'');
    }
}
