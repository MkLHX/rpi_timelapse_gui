<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191212134720 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__ftptransfert AS SELECT id, host, login, password, path, active FROM ftptransfert');
        $this->addSql('DROP TABLE ftptransfert');
        $this->addSql('CREATE TABLE ftptransfert (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, host VARCHAR(255) NOT NULL COLLATE BINARY, login VARCHAR(255) NOT NULL COLLATE BINARY, password VARCHAR(255) NOT NULL COLLATE BINARY, path VARCHAR(255) NOT NULL COLLATE BINARY, active BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO ftptransfert (id, host, login, password, path, active) SELECT id, host, login, password, path, active FROM __temp__ftptransfert');
        $this->addSql('DROP TABLE __temp__ftptransfert');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__ftptransfert AS SELECT id, host, login, password, path, active FROM ftptransfert');
        $this->addSql('DROP TABLE ftptransfert');
        $this->addSql('CREATE TABLE ftptransfert (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, host VARCHAR(255) NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, active BOOLEAN DEFAULT NULL)');
        $this->addSql('INSERT INTO ftptransfert (id, host, login, password, path, active) SELECT id, host, login, password, path, active FROM __temp__ftptransfert');
        $this->addSql('DROP TABLE __temp__ftptransfert');
    }
}
