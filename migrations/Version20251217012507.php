<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217012507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE job_offer_skill (id INT AUTO_INCREMENT NOT NULL, required_level VARCHAR(255) NOT NULL, is_required TINYINT(1) NOT NULL, job_offer_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_BE7C82D73481D195 (job_offer_id), INDEX IDX_BE7C82D75585C142 (skill_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE job_offer_skill ADD CONSTRAINT FK_BE7C82D73481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id)');
        $this->addSql('ALTER TABLE job_offer_skill ADD CONSTRAINT FK_BE7C82D75585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_offer_skill DROP FOREIGN KEY FK_BE7C82D73481D195');
        $this->addSql('ALTER TABLE job_offer_skill DROP FOREIGN KEY FK_BE7C82D75585C142');
        $this->addSql('DROP TABLE job_offer_skill');
    }
}
