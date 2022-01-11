<?php

declare(strict_types=1);

namespace Peon\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211120133311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_process_result RENAME COLUMN result_command TO command');
        $this->addSql('ALTER TABLE job_process_result RENAME COLUMN result_exit_code TO exit_code');
        $this->addSql('ALTER TABLE job_process_result RENAME COLUMN result_execution_time TO execution_time');
        $this->addSql('ALTER TABLE job_process_result RENAME COLUMN result_output TO output');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE job_process_result RENAME COLUMN command TO result_command');
        $this->addSql('ALTER TABLE job_process_result RENAME COLUMN exit_code TO result_exit_code');
        $this->addSql('ALTER TABLE job_process_result RENAME COLUMN execution_time TO result_execution_time');
        $this->addSql('ALTER TABLE job_process_result RENAME COLUMN output TO result_output');
    }
}
