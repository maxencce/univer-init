<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015094456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_694309e477153098');
        $this->addSql('ALTER TABLE site ADD code_site VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE site ADD ville VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD region_client VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD activite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE site RENAME COLUMN code TO code_gesec');
        $this->addSql('ALTER TABLE site RENAME COLUMN description TO nom');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_694309E4971B3B34 ON site (code_gesec)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_694309E4971B3B34');
        $this->addSql('ALTER TABLE site ADD code VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE site DROP code_gesec');
        $this->addSql('ALTER TABLE site DROP code_site');
        $this->addSql('ALTER TABLE site DROP ville');
        $this->addSql('ALTER TABLE site DROP region_client');
        $this->addSql('ALTER TABLE site DROP activite');
        $this->addSql('ALTER TABLE site RENAME COLUMN nom TO description');
        $this->addSql('CREATE UNIQUE INDEX uniq_694309e477153098 ON site (code)');
    }
}
