<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250127000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create announcement table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE announcement (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            is_published TINYINT(1) NOT NULL,
            published_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            type VARCHAR(50) DEFAULT NULL,
            priority INT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE announcement');
    }
}
