<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416072624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer ADD status VARCHAR(255) NOT NULL, CHANGE date date DATETIME NOT NULL, CHANGE content content LONGTEXT NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE tag tag VARCHAR(255) NOT NULL, CHANGE filename filename VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer DROP status, CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE date date DATETIME DEFAULT NULL, CHANGE content content LONGTEXT DEFAULT NULL, CHANGE tag tag VARCHAR(255) DEFAULT NULL, CHANGE filename filename VARCHAR(255) DEFAULT NULL');
    }
}
