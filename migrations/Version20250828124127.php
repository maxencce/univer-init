<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828124127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adherent (adherent_id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(adherent_id))');
        $this->addSql('CREATE TABLE affaire_adherent (affaire_version_id INT NOT NULL, adherent_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(affaire_version_id, adherent_id))');
        $this->addSql('CREATE INDEX IDX_C04DD22721E4EFBD ON affaire_adherent (affaire_version_id)');
        $this->addSql('CREATE INDEX IDX_C04DD22725F06C53 ON affaire_adherent (adherent_id)');
        $this->addSql('CREATE TABLE affaire_bpu (affaire_bpu_id SERIAL NOT NULL, affaire_version_id INT NOT NULL, type VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, puissance VARCHAR(50) DEFAULT NULL, temps_maintenance_annuelle DOUBLE PRECISION DEFAULT NULL, nb_heures_visite_technique DOUBLE PRECISION DEFAULT NULL, nb_heures_visite_bon_fonctionnement TEXT DEFAULT NULL, total_heures_equipement TEXT DEFAULT NULL, prix_mo DOUBLE PRECISION DEFAULT NULL, frais_kilometrique_unitaire DOUBLE PRECISION DEFAULT NULL, nb_km_moyen INT DEFAULT NULL, total_frais_kilometrique TEXT DEFAULT NULL, divers_consommable DOUBLE PRECISION DEFAULT NULL, prix_equipement_1_visite_technique TEXT DEFAULT NULL, prix_total_equipement TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(affaire_bpu_id))');
        $this->addSql('CREATE INDEX IDX_CB67751621E4EFBD ON affaire_bpu (affaire_version_id)');
        $this->addSql('CREATE INDEX IDX_CB67751621E4EFBD497DD6348CDE5729 ON affaire_bpu (affaire_version_id, categorie, type)');
        $this->addSql('CREATE TABLE affaire_master (id SERIAL NOT NULL, client_id INT DEFAULT NULL, current_version_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, archive BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1AB0631677153098 ON affaire_master (code)');
        $this->addSql('CREATE INDEX IDX_1AB0631619EB6921 ON affaire_master (client_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1AB063169407EE77 ON affaire_master (current_version_id)');
        $this->addSql('CREATE TABLE affaire_site_adherent (affaire_version_id INT NOT NULL, site_id INT NOT NULL, adherent_id INT NOT NULL, sous_traitant_id INT DEFAULT NULL, activite VARCHAR(255) DEFAULT \'MULTITECH\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(affaire_version_id, site_id, adherent_id))');
        $this->addSql('CREATE INDEX IDX_314064F821E4EFBD ON affaire_site_adherent (affaire_version_id)');
        $this->addSql('CREATE INDEX IDX_314064F8F6BD1646 ON affaire_site_adherent (site_id)');
        $this->addSql('CREATE INDEX IDX_314064F825F06C53 ON affaire_site_adherent (adherent_id)');
        $this->addSql('CREATE INDEX IDX_314064F89395527E ON affaire_site_adherent (sous_traitant_id)');
        $this->addSql('CREATE TABLE affaire_version (id SERIAL NOT NULL, master_id INT NOT NULL, version_number INT NOT NULL, statut VARCHAR(255) DEFAULT \'OFFRE\' NOT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, archive BOOLEAN NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_244FA8B313B3DB11 ON affaire_version (master_id)');
        $this->addSql('CREATE TABLE client (client_id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, adresse TEXT NOT NULL, siren VARCHAR(255) NOT NULL, contact_nom VARCHAR(255) NOT NULL, contact_email VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(client_id))');
        $this->addSql('CREATE TABLE contrat_application (affaire_version_id INT NOT NULL, site_id INT NOT NULL, affaire_bpu_id INT NOT NULL, quantite INT NOT NULL, prix_maintenance_site NUMERIC(10, 2) DEFAULT NULL, nb_visites_technique INT DEFAULT NULL, nb_visites_bon_fonctionnement INT DEFAULT NULL, prix_visite_technique NUMERIC(10, 2) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(affaire_version_id, site_id, affaire_bpu_id))');
        $this->addSql('CREATE INDEX IDX_7E8653E421E4EFBD ON contrat_application (affaire_version_id)');
        $this->addSql('CREATE INDEX IDX_7E8653E4F6BD1646 ON contrat_application (site_id)');
        $this->addSql('CREATE INDEX IDX_7E8653E4FF746371 ON contrat_application (affaire_bpu_id)');
        $this->addSql('CREATE TABLE site (site_id SERIAL NOT NULL, client_id INT NOT NULL, code VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, adresse TEXT DEFAULT NULL, code_postal VARCHAR(20) DEFAULT NULL, contact_nom VARCHAR(255) DEFAULT NULL, contact_email VARCHAR(255) DEFAULT NULL, statut VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(site_id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_694309E477153098 ON site (code)');
        $this->addSql('CREATE INDEX IDX_694309E419EB6921 ON site (client_id)');
        $this->addSql('CREATE TABLE utilisateur (user_id SERIAL NOT NULL, adherent_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, enabled BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(user_id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)');
        $this->addSql('CREATE INDEX IDX_1D1C63B325F06C53 ON utilisateur (adherent_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE affaire_adherent ADD CONSTRAINT FK_C04DD22721E4EFBD FOREIGN KEY (affaire_version_id) REFERENCES affaire_version (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE affaire_adherent ADD CONSTRAINT FK_C04DD22725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (adherent_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE affaire_bpu ADD CONSTRAINT FK_CB67751621E4EFBD FOREIGN KEY (affaire_version_id) REFERENCES affaire_version (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE affaire_master ADD CONSTRAINT FK_1AB0631619EB6921 FOREIGN KEY (client_id) REFERENCES client (client_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE affaire_master ADD CONSTRAINT FK_1AB063169407EE77 FOREIGN KEY (current_version_id) REFERENCES affaire_version (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE affaire_site_adherent ADD CONSTRAINT FK_314064F821E4EFBD FOREIGN KEY (affaire_version_id) REFERENCES affaire_version (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE affaire_site_adherent ADD CONSTRAINT FK_314064F8F6BD1646 FOREIGN KEY (site_id) REFERENCES site (site_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE affaire_site_adherent ADD CONSTRAINT FK_314064F825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (adherent_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE affaire_site_adherent ADD CONSTRAINT FK_314064F89395527E FOREIGN KEY (sous_traitant_id) REFERENCES adherent (adherent_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE affaire_version ADD CONSTRAINT FK_244FA8B313B3DB11 FOREIGN KEY (master_id) REFERENCES affaire_master (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contrat_application ADD CONSTRAINT FK_7E8653E421E4EFBD FOREIGN KEY (affaire_version_id) REFERENCES affaire_version (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contrat_application ADD CONSTRAINT FK_7E8653E4F6BD1646 FOREIGN KEY (site_id) REFERENCES site (site_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contrat_application ADD CONSTRAINT FK_7E8653E4FF746371 FOREIGN KEY (affaire_bpu_id) REFERENCES affaire_bpu (affaire_bpu_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E419EB6921 FOREIGN KEY (client_id) REFERENCES client (client_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B325F06C53 FOREIGN KEY (adherent_id) REFERENCES adherent (adherent_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE affaire_adherent DROP CONSTRAINT FK_C04DD22721E4EFBD');
        $this->addSql('ALTER TABLE affaire_adherent DROP CONSTRAINT FK_C04DD22725F06C53');
        $this->addSql('ALTER TABLE affaire_bpu DROP CONSTRAINT FK_CB67751621E4EFBD');
        $this->addSql('ALTER TABLE affaire_master DROP CONSTRAINT FK_1AB0631619EB6921');
        $this->addSql('ALTER TABLE affaire_master DROP CONSTRAINT FK_1AB063169407EE77');
        $this->addSql('ALTER TABLE affaire_site_adherent DROP CONSTRAINT FK_314064F821E4EFBD');
        $this->addSql('ALTER TABLE affaire_site_adherent DROP CONSTRAINT FK_314064F8F6BD1646');
        $this->addSql('ALTER TABLE affaire_site_adherent DROP CONSTRAINT FK_314064F825F06C53');
        $this->addSql('ALTER TABLE affaire_site_adherent DROP CONSTRAINT FK_314064F89395527E');
        $this->addSql('ALTER TABLE affaire_version DROP CONSTRAINT FK_244FA8B313B3DB11');
        $this->addSql('ALTER TABLE contrat_application DROP CONSTRAINT FK_7E8653E421E4EFBD');
        $this->addSql('ALTER TABLE contrat_application DROP CONSTRAINT FK_7E8653E4F6BD1646');
        $this->addSql('ALTER TABLE contrat_application DROP CONSTRAINT FK_7E8653E4FF746371');
        $this->addSql('ALTER TABLE site DROP CONSTRAINT FK_694309E419EB6921');
        $this->addSql('ALTER TABLE utilisateur DROP CONSTRAINT FK_1D1C63B325F06C53');
        $this->addSql('DROP TABLE adherent');
        $this->addSql('DROP TABLE affaire_adherent');
        $this->addSql('DROP TABLE affaire_bpu');
        $this->addSql('DROP TABLE affaire_master');
        $this->addSql('DROP TABLE affaire_site_adherent');
        $this->addSql('DROP TABLE affaire_version');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE contrat_application');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
