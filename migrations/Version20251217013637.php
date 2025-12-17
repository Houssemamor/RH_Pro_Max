<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217013637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidate_profile_skill (id INT AUTO_INCREMENT NOT NULL, level VARCHAR(255) NOT NULL, years_experience INT DEFAULT NULL, candidate_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_879F608891BD8781 (candidate_id), INDEX IDX_879F60885585C142 (skill_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE candidate_profile_skill ADD CONSTRAINT FK_879F608891BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id)');
        $this->addSql('ALTER TABLE candidate_profile_skill ADD CONSTRAINT FK_879F60885585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_profile_skill DROP FOREIGN KEY FK_879F608891BD8781');
        $this->addSql('ALTER TABLE candidate_profile_skill DROP FOREIGN KEY FK_879F60885585C142');
        $this->addSql('DROP TABLE candidate_profile_skill');
    }
}
