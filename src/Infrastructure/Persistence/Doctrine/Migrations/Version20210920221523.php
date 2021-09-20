<?php

declare(strict_types=1);

namespace PHPMate\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210920221523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE job (job_id UUID NOT NULL, project_id UUID NOT NULL, task_id UUID NOT NULL, task_name VARCHAR(255) NOT NULL, scheduled_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, succeeded_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, failed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, commands JSON NOT NULL, PRIMARY KEY(job_id))');
        $this->addSql('COMMENT ON COLUMN job.job_id IS \'(DC2Type:job_id)\'');
        $this->addSql('COMMENT ON COLUMN job.project_id IS \'(DC2Type:project_id)\'');
        $this->addSql('COMMENT ON COLUMN job.task_id IS \'(DC2Type:task_id)\'');
        $this->addSql('COMMENT ON COLUMN job.scheduled_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN job.started_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN job.succeeded_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN job.failed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE job_process (job_id UUID NOT NULL, "order" INT NOT NULL, result_command VARCHAR(255) NOT NULL, result_exit_code INT NOT NULL, result_execution_time DOUBLE PRECISION NOT NULL, result_output TEXT NOT NULL, PRIMARY KEY("order", job_id))');
        $this->addSql('CREATE INDEX IDX_1CF2AF0DBE04EA9 ON job_process (job_id)');
        $this->addSql('COMMENT ON COLUMN job_process.job_id IS \'(DC2Type:job_id)\'');
        $this->addSql('CREATE TABLE project (project_id UUID NOT NULL, name VARCHAR(255) NOT NULL, remote_git_repository_repository_uri VARCHAR(255) NOT NULL, remote_git_repository_authentication_username VARCHAR(255) NOT NULL, remote_git_repository_authentication_password VARCHAR(255) NOT NULL, PRIMARY KEY(project_id))');
        $this->addSql('COMMENT ON COLUMN project.project_id IS \'(DC2Type:project_id)\'');
        $this->addSql('CREATE TABLE task (task_id UUID NOT NULL, project_id UUID NOT NULL, name VARCHAR(255) NOT NULL, commands JSON NOT NULL, schedule VARCHAR(255) DEFAULT NULL, PRIMARY KEY(task_id))');
        $this->addSql('COMMENT ON COLUMN task.task_id IS \'(DC2Type:task_id)\'');
        $this->addSql('COMMENT ON COLUMN task.project_id IS \'(DC2Type:project_id)\'');
        $this->addSql('COMMENT ON COLUMN task.schedule IS \'(DC2Type:cron_expression)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE job_process ADD CONSTRAINT FK_1CF2AF0DBE04EA9 FOREIGN KEY (job_id) REFERENCES job (job_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE job_process DROP CONSTRAINT FK_1CF2AF0DBE04EA9');
        $this->addSql('DROP TABLE job');
        $this->addSql('DROP TABLE job_process');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
