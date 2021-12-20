<?php

declare(strict_types=1);

namespace PHPMate\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211220203931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job ADD canceled_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN job.canceled_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE project DROP baselines');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE job DROP canceled_at');
        $this->addSql('ALTER TABLE project ADD baselines JSON DEFAULT \'[]\' NOT NULL');
    }
}
