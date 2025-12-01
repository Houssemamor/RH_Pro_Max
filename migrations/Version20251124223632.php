<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124223632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidate_profile (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, job_offer_id INT NOT NULL, internal_applicant_id INT DEFAULT NULL, INDEX IDX_E8607AE3481D195 (job_offer_id), INDEX IDX_E8607AEAF488EE (internal_applicant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE candidate_skill (id INT AUTO_INCREMENT NOT NULL, level VARCHAR(255) NOT NULL, confidence DOUBLE PRECISION NOT NULL, candidate_profile_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_66DD0F8BFE3D0586 (candidate_profile_id), INDEX IDX_66DD0F8B5585C142 (skill_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE cv (id INT AUTO_INCREMENT NOT NULL, file_path VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, parsed_at DATETIME DEFAULT NULL, candidate_profile_id INT NOT NULL, INDEX IDX_B66FFE92FE3D0586 (candidate_profile_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE employee_skill (id INT AUTO_INCREMENT NOT NULL, level VARCHAR(255) NOT NULL, years_experience INT NOT NULL, last_used_at DATE DEFAULT NULL, user_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_B630E90EA76ED395 (user_id), INDEX IDX_B630E90E5585C142 (skill_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE job_offer (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, location VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, closing_date DATETIME NOT NULL, creator_id INT NOT NULL, INDEX IDX_288A3A4E61220EA6 (creator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, category_id INT NOT NULL, INDEX IDX_5E3DE47712469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE skill_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, status VARCHAR(255) DEFAULT NULL, role VARCHAR(255) DEFAULT NULL, matricule VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, birthday DATE DEFAULT NULL, hire_date DATE DEFAULT NULL, department VARCHAR(255) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE candidate_profile ADD CONSTRAINT FK_E8607AE3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id)');
        $this->addSql('ALTER TABLE candidate_profile ADD CONSTRAINT FK_E8607AEAF488EE FOREIGN KEY (internal_applicant_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE candidate_skill ADD CONSTRAINT FK_66DD0F8BFE3D0586 FOREIGN KEY (candidate_profile_id) REFERENCES candidate_profile (id)');
        $this->addSql('ALTER TABLE candidate_skill ADD CONSTRAINT FK_66DD0F8B5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE92FE3D0586 FOREIGN KEY (candidate_profile_id) REFERENCES candidate_profile (id)');
        $this->addSql('ALTER TABLE employee_skill ADD CONSTRAINT FK_B630E90EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE employee_skill ADD CONSTRAINT FK_B630E90E5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE47712469DE2 FOREIGN KEY (category_id) REFERENCES skill_category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_profile DROP FOREIGN KEY FK_E8607AE3481D195');
        $this->addSql('ALTER TABLE candidate_profile DROP FOREIGN KEY FK_E8607AEAF488EE');
        $this->addSql('ALTER TABLE candidate_skill DROP FOREIGN KEY FK_66DD0F8BFE3D0586');
        $this->addSql('ALTER TABLE candidate_skill DROP FOREIGN KEY FK_66DD0F8B5585C142');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE92FE3D0586');
        $this->addSql('ALTER TABLE employee_skill DROP FOREIGN KEY FK_B630E90EA76ED395');
        $this->addSql('ALTER TABLE employee_skill DROP FOREIGN KEY FK_B630E90E5585C142');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E61220EA6');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE47712469DE2');
        $this->addSql('DROP TABLE candidate_profile');
        $this->addSql('DROP TABLE candidate_skill');
        $this->addSql('DROP TABLE cv');
        $this->addSql('DROP TABLE employee_skill');
        $this->addSql('DROP TABLE job_offer');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE skill_category');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
