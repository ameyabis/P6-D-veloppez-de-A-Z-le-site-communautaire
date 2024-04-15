<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240415153014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profil_picture DROP FOREIGN KEY profil_picture_ibfk_1');
        $this->addSql('DROP TABLE profil_picture');
        $this->addSql('ALTER TABLE comment CHANGE trick_id trick_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comment RENAME INDEX id_user TO IDX_9474526CA76ED395');
        $this->addSql('ALTER TABLE comment RENAME INDEX id_trick TO IDX_9474526CB281BE2E');
        $this->addSql('ALTER TABLE picture CHANGE trick_id trick_id INT DEFAULT NULL, CHANGE url url VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE picture RENAME INDEX id_trick TO IDX_16DB4F89B281BE2E');
        $this->addSql('ALTER TABLE trick CHANGE user_id user_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE trick RENAME INDEX id_user TO IDX_D8F0A91EA76ED395');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(180) NOT NULL, CHANGE profile_picture profile_picture VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('ALTER TABLE video CHANGE trick_id trick_id INT DEFAULT NULL, CHANGE url url VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE video RENAME INDEX id _trick TO IDX_7CC7DA2CB281BE2E');
        $this->addSql('ALTER TABLE messenger_messages ADD delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP delivred_at, CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE profil_picture (id INT AUTO_INCREMENT NOT NULL, id_user INT NOT NULL, url LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, INDEX id_user (id_user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE profil_picture ADD CONSTRAINT profil_picture_ibfk_1 FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment CHANGE trick_id trick_id INT NOT NULL');
        $this->addSql('ALTER TABLE comment RENAME INDEX idx_9474526cb281be2e TO id_trick');
        $this->addSql('ALTER TABLE comment RENAME INDEX idx_9474526ca76ed395 TO id_user');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages ADD delivred_at DATETIME DEFAULT NULL, DROP delivered_at, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE picture CHANGE trick_id trick_id INT NOT NULL, CHANGE url url LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE picture RENAME INDEX idx_16db4f89b281be2e TO id_trick');
        $this->addSql('ALTER TABLE trick CHANGE user_id user_id INT NOT NULL, CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE trick RENAME INDEX idx_d8f0a91ea76ed395 TO id_user');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677 ON user');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(255) NOT NULL, CHANGE profile_picture profile_picture LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE video CHANGE trick_id trick_id INT NOT NULL, CHANGE url url LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE video RENAME INDEX idx_7cc7da2cb281be2e TO id _trick');
    }
}
