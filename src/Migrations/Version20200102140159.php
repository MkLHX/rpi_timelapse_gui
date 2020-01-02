<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200102140159 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE ftptransfert (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, host VARCHAR(255) NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL)');
        $this->addSql('CREATE TABLE timelapse (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, resolution VARCHAR(255) NOT NULL, file_extension VARCHAR(255) NOT NULL, schedule VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE ftptransfert');
        $this->addSql('DROP TABLE timelapse');
    }
}
