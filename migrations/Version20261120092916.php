<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120092916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE episode (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rick_morty_id INTEGER NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DDAA1CDADE62C85A ON episode (rick_morty_id)');
        $this->addSql('CREATE TABLE espece (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1A2A1B16C6E55B5 ON espece (nom)');
        $this->addSql('CREATE TABLE lieu (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rick_morty_id INTEGER NOT NULL, nom VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2F577D59DE62C85A ON lieu (rick_morty_id)');
        $this->addSql('CREATE TABLE personnage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rick_morty_id INTEGER NOT NULL, nom VARCHAR(255) NOT NULL, statut VARCHAR(20) NOT NULL, genre VARCHAR(20) NOT NULL, sous_type VARCHAR(100) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, cree_le DATETIME DEFAULT NULL, espece_id INTEGER DEFAULT NULL, origine_id INTEGER DEFAULT NULL, localisation_id INTEGER DEFAULT NULL, CONSTRAINT FK_6AEA486D2D191E7A FOREIGN KEY (espece_id) REFERENCES espece (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6AEA486D87998E FOREIGN KEY (origine_id) REFERENCES lieu (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6AEA486DC68BE09C FOREIGN KEY (localisation_id) REFERENCES lieu (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AEA486DDE62C85A ON personnage (rick_morty_id)');
        $this->addSql('CREATE INDEX IDX_6AEA486D2D191E7A ON personnage (espece_id)');
        $this->addSql('CREATE INDEX IDX_6AEA486D87998E ON personnage (origine_id)');
        $this->addSql('CREATE INDEX IDX_6AEA486DC68BE09C ON personnage (localisation_id)');
        $this->addSql('CREATE TABLE personnage_episode (personnage_id INTEGER NOT NULL, episode_id INTEGER NOT NULL, PRIMARY KEY (personnage_id, episode_id), CONSTRAINT FK_F1B524DB5E315342 FOREIGN KEY (personnage_id) REFERENCES personnage (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F1B524DB362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F1B524DB5E315342 ON personnage_episode (personnage_id)');
        $this->addSql('CREATE INDEX IDX_F1B524DB362B62A0 ON personnage_episode (episode_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE espece');
        $this->addSql('DROP TABLE lieu');
        $this->addSql('DROP TABLE personnage');
        $this->addSql('DROP TABLE personnage_episode');
    }
}
