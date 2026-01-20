<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119161646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, active TINYINT NOT NULL, type VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, image_id INT NOT NULL, author_id INT NOT NULL, character_rel_id INT NOT NULL, INDEX IDX_23A0E663DA5256D (image_id), INDEX IDX_23A0E66F675F31B (author_id), UNIQUE INDEX UNIQ_23A0E66ED48625E (character_rel_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `character` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, genre VARCHAR(12) NOT NULL, skin_color VARCHAR(50) NOT NULL, eyes_color VARCHAR(50) NOT NULL, hair_color VARCHAR(50) NOT NULL, face VARCHAR(50) NOT NULL, hair VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE character_equipment (character_id INT NOT NULL, equipment_id INT NOT NULL, INDEX IDX_546877B81136BE75 (character_id), INDEX IDX_546877B8517FE9FE (equipment_id), PRIMARY KEY (character_id, equipment_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, note INT NOT NULL, content VARCHAR(255) NOT NULL, date_comment DATE NOT NULL, status VARCHAR(50) NOT NULL, article_id INT NOT NULL, author_id INT NOT NULL, INDEX IDX_9474526C7294869C (article_id), INDEX IDX_9474526CF675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, type VARCHAR(50) NOT NULL, slot VARCHAR(50) NOT NULL, z_index INT NOT NULL, image_id INT NOT NULL, INDEX IDX_D338D5833DA5256D (image_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, source VARCHAR(255) NOT NULL, size INT NOT NULL, extension VARCHAR(12) NOT NULL, type VARCHAR(50) NOT NULL, width INT NOT NULL, height INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(50) NOT NULL, content VARCHAR(255) NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, recipient_id INT NOT NULL, INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307FE92F8F78 (recipient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, mail VARCHAR(120) NOT NULL, password VARCHAR(255) NOT NULL, suspended TINYINT NOT NULL, change_password TINYINT NOT NULL, roles JSON NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E663DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66ED48625E FOREIGN KEY (character_rel_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE character_equipment ADD CONSTRAINT FK_546877B81136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_equipment ADD CONSTRAINT FK_546877B8517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D5833DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FE92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E663DA5256D');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F675F31B');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66ED48625E');
        $this->addSql('ALTER TABLE character_equipment DROP FOREIGN KEY FK_546877B81136BE75');
        $this->addSql('ALTER TABLE character_equipment DROP FOREIGN KEY FK_546877B8517FE9FE');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7294869C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D5833DA5256D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FE92F8F78');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE `character`');
        $this->addSql('DROP TABLE character_equipment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE user');
    }
}
