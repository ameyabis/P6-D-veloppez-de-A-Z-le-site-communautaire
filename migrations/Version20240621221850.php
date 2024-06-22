<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240621221850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment CHANGE trick_id trick_id INT NOT NULL');
        $this->addSql('ALTER TABLE trick ADD groups_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91EF373DCF FOREIGN KEY (groups_id) REFERENCES groups (id)');
        $this->addSql('CREATE INDEX IDX_D8F0A91EF373DCF ON trick (groups_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91EF373DCF');
        $this->addSql('DROP TABLE groups');
        $this->addSql('ALTER TABLE comment CHANGE trick_id trick_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_D8F0A91EF373DCF ON trick');
        $this->addSql('ALTER TABLE trick DROP groups_id');
    }
}
