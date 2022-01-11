<?php

declare(strict_types=1);

namespace Peon\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211120132004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_process RENAME TO job_process_result');
        $this->addSql('ALTER INDEX idx_1cf2af0dbe04ea9 RENAME TO IDX_4BFF0F71BE04EA9');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_process_result RENAME TO job_process');
        // $this->addSql('ALTER INDEX idx_4bff0f71be04ea9 RENAME TO idx_1cf2af0dbe04ea9');
    }
}
