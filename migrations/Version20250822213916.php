<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250822213916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_message (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, pays VARCHAR(255) DEFAULT NULL, sujet VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, newsletter TINYINT(1) NOT NULL, rgpd TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', statut VARCHAR(255) DEFAULT \'nouveau\' NOT NULL, reponse LONGTEXT DEFAULT NULL, repondu_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', repondu_par VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE contact_message');
    }
}
