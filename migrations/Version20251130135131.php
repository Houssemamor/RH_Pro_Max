<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251130135131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE visitor (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone VARCHAR(20) DEFAULT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, last_login_at DATETIME DEFAULT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_VISITOR_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE candidate_profile ADD visitor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE candidate_profile ADD CONSTRAINT FK_E8607AE70BEE6D FOREIGN KEY (visitor_id) REFERENCES visitor (id)');
        $this->addSql('CREATE INDEX IDX_E8607AE70BEE6D ON candidate_profile (visitor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE visitor');
        $this->addSql('ALTER TABLE candidate_profile DROP FOREIGN KEY FK_E8607AE70BEE6D');
        $this->addSql('DROP INDEX IDX_E8607AE70BEE6D ON candidate_profile');
        $this->addSql('ALTER TABLE candidate_profile DROP visitor_id');
    }
}
