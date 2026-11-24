<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120093710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__episode AS SELECT id, rick_morty_id FROM episode');
        $this->addSql('DROP TABLE episode');
        $this->addSql('CREATE TABLE episode (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rick_morty_id INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO episode (id, rick_morty_id) SELECT id, rick_morty_id FROM __temp__episode');
        $this->addSql('DROP TABLE __temp__episode');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DDAA1CDADE62C85A ON episode (rick_morty_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__lieu AS SELECT id, rick_morty_id, nom FROM lieu');
        $this->addSql('DROP TABLE lieu');
        $this->addSql('CREATE TABLE lieu (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rick_morty_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO lieu (id, rick_morty_id, nom) SELECT id, rick_morty_id, nom FROM __temp__lieu');
        $this->addSql('DROP TABLE __temp__lieu');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2F577D59DE62C85A ON lieu (rick_morty_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__personnage AS SELECT id, rick_morty_id, nom, statut, genre, sous_type, image, cree_le, espece_id, origine_id, localisation_id FROM personnage');
        $this->addSql('DROP TABLE personnage');
        $this->addSql('CREATE TABLE personnage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rick_morty_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, statut VARCHAR(20) NOT NULL, genre VARCHAR(20) NOT NULL, sous_type VARCHAR(100) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, cree_le DATETIME DEFAULT NULL, espece_id INTEGER DEFAULT NULL, origine_id INTEGER DEFAULT NULL, localisation_id INTEGER DEFAULT NULL, CONSTRAINT FK_6AEA486D2D191E7A FOREIGN KEY (espece_id) REFERENCES espece (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6AEA486D87998E FOREIGN KEY (origine_id) REFERENCES lieu (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6AEA486DC68BE09C FOREIGN KEY (localisation_id) REFERENCES lieu (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO personnage (id, rick_morty_id, nom, statut, genre, sous_type, image, cree_le, espece_id, origine_id, localisation_id) SELECT id, rick_morty_id, nom, statut, genre, sous_type, image, cree_le, espece_id, origine_id, localisation_id FROM __temp__personnage');
        $this->addSql('DROP TABLE __temp__personnage');
        $this->addSql('CREATE INDEX IDX_6AEA486DC68BE09C ON personnage (localisation_id)');
        $this->addSql('CREATE INDEX IDX_6AEA486D87998E ON personnage (origine_id)');
        $this->addSql('CREATE INDEX IDX_6AEA486D2D191E7A ON personnage (espece_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AEA486DDE62C85A ON personnage (rick_morty_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__episode AS SELECT id, rick_morty_id FROM episode');
        $this->addSql('DROP TABLE episode');
        $this->addSql('CREATE TABLE episode (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rick_morty_id INTEGER NOT NULL)');
        $this->addSql('INSERT INTO episode (id, rick_morty_id) SELECT id, rick_morty_id FROM __temp__episode');
        $this->addSql('DROP TABLE __temp__episode');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DDAA1CDADE62C85A ON episode (rick_morty_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__lieu AS SELECT id, rick_morty_id, nom FROM lieu');
        $this->addSql('DROP TABLE lieu');
        $this->addSql('CREATE TABLE lieu (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rick_morty_id INTEGER NOT NULL, nom VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO lieu (id, rick_morty_id, nom) SELECT id, rick_morty_id, nom FROM __temp__lieu');
        $this->addSql('DROP TABLE __temp__lieu');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2F577D59DE62C85A ON lieu (rick_morty_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__personnage AS SELECT id, rick_morty_id, nom, statut, genre, sous_type, image, cree_le, espece_id, origine_id, localisation_id FROM personnage');
        $this->addSql('DROP TABLE personnage');
        $this->addSql('CREATE TABLE personnage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rick_morty_id INTEGER NOT NULL, nom VARCHAR(255) NOT NULL, statut VARCHAR(20) NOT NULL, genre VARCHAR(20) NOT NULL, sous_type VARCHAR(100) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, cree_le DATETIME DEFAULT NULL, espece_id INTEGER DEFAULT NULL, origine_id INTEGER DEFAULT NULL, localisation_id INTEGER DEFAULT NULL, CONSTRAINT FK_6AEA486D2D191E7A FOREIGN KEY (espece_id) REFERENCES espece (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6AEA486D87998E FOREIGN KEY (origine_id) REFERENCES lieu (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6AEA486DC68BE09C FOREIGN KEY (localisation_id) REFERENCES lieu (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO personnage (id, rick_morty_id, nom, statut, genre, sous_type, image, cree_le, espece_id, origine_id, localisation_id) SELECT id, rick_morty_id, nom, statut, genre, sous_type, image, cree_le, espece_id, origine_id, localisation_id FROM __temp__personnage');
        $this->addSql('DROP TABLE __temp__personnage');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AEA486DDE62C85A ON personnage (rick_morty_id)');
        $this->addSql('CREATE INDEX IDX_6AEA486D2D191E7A ON personnage (espece_id)');
        $this->addSql('CREATE INDEX IDX_6AEA486D87998E ON personnage (origine_id)');
        $this->addSql('CREATE INDEX IDX_6AEA486DC68BE09C ON personnage (localisation_id)');
    }
}
