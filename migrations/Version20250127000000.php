<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250127000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create reservation table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation (
            id INT AUTO_INCREMENT NOT NULL,
            package_id INT NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            number_of_people INT NOT NULL,
            gender VARCHAR(10) NOT NULL,
            address LONGTEXT NOT NULL,
            city VARCHAR(255) NOT NULL,
            postal_code VARCHAR(10) NOT NULL,
            country VARCHAR(255) NOT NULL,
            total_price NUMERIC(10, 2) NOT NULL,
            status VARCHAR(50) NOT NULL,
            special_requests LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            payment_method VARCHAR(255) DEFAULT NULL,
            payment_status VARCHAR(255) DEFAULT NULL,
            transaction_id VARCHAR(255) DEFAULT NULL,
            INDEX IDX_42C84955F44CABFF (package_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955F44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955F44CABFF');
        $this->addSql('DROP TABLE reservation');
    }
}
