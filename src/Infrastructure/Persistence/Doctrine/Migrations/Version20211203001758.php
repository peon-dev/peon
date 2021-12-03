<?php

declare(strict_types=1);

namespace PHPMate\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211203001758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job ADD recipe_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE job ALTER task_id DROP NOT NULL');
        $this->addSql('ALTER TABLE job RENAME COLUMN task_name TO title');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE job DROP recipe_name');
        $this->addSql('ALTER TABLE job ALTER task_id SET NOT NULL');
        $this->addSql('ALTER TABLE job RENAME COLUMN title TO task_name');
    }
}
