<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191212113313 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('ALTER TABLE timelapse ADD COLUMN file_extension VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__timelapse AS SELECT id, resolution, schedule, path FROM timelapse');
        $this->addSql('DROP TABLE timelapse');
        $this->addSql('CREATE TABLE timelapse (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, resolution VARCHAR(255) NOT NULL, schedule DATETIME NOT NULL, path VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO timelapse (id, resolution, schedule, path) SELECT id, resolution, schedule, path FROM __temp__timelapse');
        $this->addSql('DROP TABLE __temp__timelapse');
    }
}
