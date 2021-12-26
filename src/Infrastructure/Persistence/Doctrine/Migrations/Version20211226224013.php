<?php

declare(strict_types=1);

namespace PHPMate\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211226224013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job ALTER recipe_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE job ALTER recipe_name DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN job.recipe_name IS \'(DC2Type:recipe_name)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE job ALTER recipe_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE job ALTER recipe_name DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN job.recipe_name IS NULL');
    }
}
