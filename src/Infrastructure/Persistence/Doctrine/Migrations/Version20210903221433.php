<?php

declare(strict_types=1);

namespace PHPMate\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210903221433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE job (job_id CHAR(36) NOT NULL COMMENT \'(DC2Type:job_id)\', project_id CHAR(36) NOT NULL COMMENT \'(DC2Type:project_id)\', task_id CHAR(36) NOT NULL COMMENT \'(DC2Type:task_id)\', task_name VARCHAR(255) NOT NULL, scheduled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', succeeded_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', failed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', commands LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(job_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_process (job_id CHAR(36) NOT NULL COMMENT \'(DC2Type:job_id)\', `order` INT NOT NULL, result_command VARCHAR(255) NOT NULL, result_exit_code INT NOT NULL, result_execution_time DOUBLE PRECISION NOT NULL, result_output LONGTEXT NOT NULL, INDEX IDX_1CF2AF0DBE04EA9 (job_id), PRIMARY KEY(`order`, job_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job_process ADD CONSTRAINT FK_1CF2AF0DBE04EA9 FOREIGN KEY (job_id) REFERENCES job (job_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_process DROP FOREIGN KEY FK_1CF2AF0DBE04EA9');
        $this->addSql('DROP TABLE job');
        $this->addSql('DROP TABLE job_process');
    }
}
