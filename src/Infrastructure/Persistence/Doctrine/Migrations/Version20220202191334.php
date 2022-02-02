<?php

declare(strict_types=1);

namespace Peon\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220202191334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE process (process_id UUID NOT NULL, job_id UUID NOT NULL, sequence INT NOT NULL, command TEXT NOT NULL, timeout_seconds INT NOT NULL, execution_time DOUBLE PRECISION DEFAULT NULL, exit_code INT DEFAULT NULL, output VARCHAR(255) DEFAULT NULL, PRIMARY KEY(process_id))');
        $this->addSql('COMMENT ON COLUMN process.process_id IS \'(DC2Type:process_id)\'');
        $this->addSql('COMMENT ON COLUMN process.job_id IS \'(DC2Type:job_id)\'');
        $this->addSql('DROP TABLE job_process_result');
        $this->addSql('ALTER TABLE job ADD merge_request JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE job DROP merge_request_url');
        $this->addSql('COMMENT ON COLUMN job.merge_request IS \'(DC2Type:merge_request)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE job_process_result (job_id UUID NOT NULL, "order" INT NOT NULL, command TEXT NOT NULL, exit_code INT NOT NULL, execution_time DOUBLE PRECISION NOT NULL, output TEXT NOT NULL, PRIMARY KEY("order", job_id))');
        $this->addSql('CREATE INDEX idx_4bff0f71be04ea9 ON job_process_result (job_id)');
        $this->addSql('COMMENT ON COLUMN job_process_result.job_id IS \'(DC2Type:job_id)\'');
        $this->addSql('ALTER TABLE job_process_result ADD CONSTRAINT fk_1cf2af0dbe04ea9 FOREIGN KEY (job_id) REFERENCES job (job_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE process');
        $this->addSql('ALTER TABLE job ADD merge_request_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE job DROP merge_request');
    }
}
