<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191212104559 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__timelapse AS SELECT id, resolution, schedule, destination FROM timelapse');
        $this->addSql('DROP TABLE timelapse');
        $this->addSql('CREATE TABLE timelapse (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, resolution VARCHAR(255) NOT NULL COLLATE BINARY, destination VARCHAR(255) NOT NULL COLLATE BINARY, schedule DATETIME NOT NULL)');
        $this->addSql('INSERT INTO timelapse (id, resolution, schedule, destination) SELECT id, resolution, schedule, destination FROM __temp__timelapse');
        $this->addSql('DROP TABLE __temp__timelapse');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__timelapse AS SELECT id, resolution, schedule, destination FROM timelapse');
        $this->addSql('DROP TABLE timelapse');
        $this->addSql('CREATE TABLE timelapse (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, resolution VARCHAR(255) NOT NULL, destination VARCHAR(255) NOT NULL, schedule VARCHAR(255) NOT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO timelapse (id, resolution, schedule, destination) SELECT id, resolution, schedule, destination FROM __temp__timelapse');
        $this->addSql('DROP TABLE __temp__timelapse');
    }
}
