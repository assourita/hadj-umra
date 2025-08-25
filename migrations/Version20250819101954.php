<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250819101954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE billet (id INT AUTO_INCREMENT NOT NULL, pelerin_id INT NOT NULL, emis_par_id INT DEFAULT NULL, pnr VARCHAR(20) DEFAULT NULL, compagnie VARCHAR(100) DEFAULT NULL, vol_aller VARCHAR(255) DEFAULT NULL, vol_retour VARCHAR(255) DEFAULT NULL, date_vol_aller DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_vol_retour DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', siege_aller VARCHAR(10) DEFAULT NULL, siege_retour VARCHAR(10) DEFAULT NULL, statut VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', remarques LONGTEXT DEFAULT NULL, date_emission DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', classe_vol VARCHAR(10) DEFAULT NULL, escales JSON DEFAULT NULL, UNIQUE INDEX UNIQ_1F034AF6CDCCDA57 (pelerin_id), INDEX IDX_1F034AF69ED91B8D (emis_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depart (id INT AUTO_INCREMENT NOT NULL, package_id INT NOT NULL, ville_depart VARCHAR(100) NOT NULL, date_depart DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_retour DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', quota_total SMALLINT NOT NULL, quota_vendu SMALLINT NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', compagnie_aerienne VARCHAR(255) DEFAULT NULL, numero_vol VARCHAR(10) DEFAULT NULL, remarques LONGTEXT DEFAULT NULL, INDEX IDX_1B3EBB08F44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, pelerin_id INT NOT NULL, valide_par_id INT DEFAULT NULL, type VARCHAR(50) NOT NULL, url VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, file_size INT DEFAULT NULL, mime_type VARCHAR(100) DEFAULT NULL, statut VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', commentaire_refus LONGTEXT DEFAULT NULL, date_validation DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D8698A76CDCCDA57 (pelerin_id), INDEX IDX_D8698A766AF12ED9 (valide_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE package (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, duree_jours SMALLINT NOT NULL, inclus LONGTEXT DEFAULT NULL, non_inclus LONGTEXT DEFAULT NULL, images JSON DEFAULT NULL, hotel_makkah VARCHAR(255) DEFAULT NULL, hotel_madinah VARCHAR(255) DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', programme LONGTEXT DEFAULT NULL, prix_base NUMERIC(10, 2) DEFAULT NULL, devise VARCHAR(3) DEFAULT NULL, UNIQUE INDEX UNIQ_DE686795989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, valide_par_id INT DEFAULT NULL, mode VARCHAR(50) NOT NULL, reference VARCHAR(255) DEFAULT NULL, montant NUMERIC(10, 2) NOT NULL, statut VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', devise VARCHAR(3) NOT NULL, stripe_payment_intent_id VARCHAR(255) DEFAULT NULL, metadata JSON DEFAULT NULL, commentaire LONGTEXT DEFAULT NULL, date_validation DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B1DC7A1EB83297E7 (reservation_id), INDEX IDX_B1DC7A1E6AF12ED9 (valide_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pelerin (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, date_naissance DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', nationalite VARCHAR(100) NOT NULL, passport_number VARCHAR(50) DEFAULT NULL, passport_expiry DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', sexe VARCHAR(10) DEFAULT NULL, lieu_naissance VARCHAR(100) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', nom_urgence VARCHAR(100) DEFAULT NULL, phone_urgence VARCHAR(20) DEFAULT NULL, relation_urgence VARCHAR(100) DEFAULT NULL, INDEX IDX_30472C4EB83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, depart_id INT NOT NULL, type_chambre VARCHAR(20) NOT NULL, nb_pelerins SMALLINT NOT NULL, total NUMERIC(10, 2) NOT NULL, statut VARCHAR(30) NOT NULL, code_dossier VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', acompte NUMERIC(10, 2) DEFAULT NULL, reste NUMERIC(10, 2) DEFAULT NULL, remarques LONGTEXT DEFAULT NULL, date_limite_document DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_42C849555EFA4AB (code_dossier), INDEX IDX_42C84955A76ED395 (user_id), INDEX IDX_42C84955AE02FE4B (depart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarif (id INT AUTO_INCREMENT NOT NULL, depart_id INT NOT NULL, type_chambre VARCHAR(20) NOT NULL, prix_base NUMERIC(10, 2) NOT NULL, devise VARCHAR(3) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', reduction NUMERIC(5, 2) DEFAULT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_E7189C9AE02FE4B (depart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, phone VARCHAR(20) DEFAULT NULL, pays VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE visa (id INT AUTO_INCREMENT NOT NULL, pelerin_id INT NOT NULL, traite_par_id INT DEFAULT NULL, statut VARCHAR(30) NOT NULL, numero VARCHAR(50) DEFAULT NULL, submitted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', approved_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_expiration DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', commentaire LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', reference_consulat VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_16B1AB08CDCCDA57 (pelerin_id), INDEX IDX_16B1AB08167FABE8 (traite_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE billet ADD CONSTRAINT FK_1F034AF6CDCCDA57 FOREIGN KEY (pelerin_id) REFERENCES pelerin (id)');
        $this->addSql('ALTER TABLE billet ADD CONSTRAINT FK_1F034AF69ED91B8D FOREIGN KEY (emis_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE depart ADD CONSTRAINT FK_1B3EBB08F44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76CDCCDA57 FOREIGN KEY (pelerin_id) REFERENCES pelerin (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A766AF12ED9 FOREIGN KEY (valide_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E6AF12ED9 FOREIGN KEY (valide_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE pelerin ADD CONSTRAINT FK_30472C4EB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955AE02FE4B FOREIGN KEY (depart_id) REFERENCES depart (id)');
        $this->addSql('ALTER TABLE tarif ADD CONSTRAINT FK_E7189C9AE02FE4B FOREIGN KEY (depart_id) REFERENCES depart (id)');
        $this->addSql('ALTER TABLE visa ADD CONSTRAINT FK_16B1AB08CDCCDA57 FOREIGN KEY (pelerin_id) REFERENCES pelerin (id)');
        $this->addSql('ALTER TABLE visa ADD CONSTRAINT FK_16B1AB08167FABE8 FOREIGN KEY (traite_par_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE billet DROP FOREIGN KEY FK_1F034AF6CDCCDA57');
        $this->addSql('ALTER TABLE billet DROP FOREIGN KEY FK_1F034AF69ED91B8D');
        $this->addSql('ALTER TABLE depart DROP FOREIGN KEY FK_1B3EBB08F44CABFF');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76CDCCDA57');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A766AF12ED9');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EB83297E7');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E6AF12ED9');
        $this->addSql('ALTER TABLE pelerin DROP FOREIGN KEY FK_30472C4EB83297E7');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955AE02FE4B');
        $this->addSql('ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C9AE02FE4B');
        $this->addSql('ALTER TABLE visa DROP FOREIGN KEY FK_16B1AB08CDCCDA57');
        $this->addSql('ALTER TABLE visa DROP FOREIGN KEY FK_16B1AB08167FABE8');
        $this->addSql('DROP TABLE billet');
        $this->addSql('DROP TABLE depart');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE package');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE pelerin');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE tarif');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE visa');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
