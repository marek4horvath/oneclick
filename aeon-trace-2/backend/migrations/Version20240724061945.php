<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240724061945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add supply chain and supply chain template.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE supply_chain_template (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dpp ADD supply_chain_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE company_id company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE company_site_id company_site_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B2243EB7476B FOREIGN KEY (supply_chain_id) REFERENCES supply_chain (id)');
        $this->addSql('CREATE INDEX IDX_CFA5B2243EB7476B ON dpp (supply_chain_id)');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845979B1AD6');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE8453EB7476B');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845D6EF0C91');
        $this->addSql('DROP INDEX IDX_857FE845979B1AD6 ON node');
        $this->addSql('DROP INDEX IDX_857FE845D6EF0C91 ON node');
        $this->addSql('DROP INDEX IDX_857FE8453EB7476B ON node');
        $this->addSql('ALTER TABLE node ADD supply_chain_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP company_id, DROP company_site_id, DROP supply_chain_id, DROP qr_image, DROP create_qr');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE8455D0D6533 FOREIGN KEY (supply_chain_template_id) REFERENCES supply_chain_template (id)');
        $this->addSql('CREATE INDEX IDX_857FE8455D0D6533 ON node (supply_chain_template_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE8455D0D6533');
        $this->addSql('DROP TABLE supply_chain_template');
        $this->addSql('DROP INDEX IDX_857FE8455D0D6533 ON node');
        $this->addSql('ALTER TABLE node ADD company_site_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD supply_chain_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD qr_image VARCHAR(255) DEFAULT \'\', ADD create_qr TINYINT(1) DEFAULT 0 NOT NULL, CHANGE supply_chain_template_id company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE8453EB7476B FOREIGN KEY (supply_chain_id) REFERENCES supply_chain (id)');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845D6EF0C91 FOREIGN KEY (company_site_id) REFERENCES company_site (id)');
        $this->addSql('CREATE INDEX IDX_857FE845979B1AD6 ON node (company_id)');
        $this->addSql('CREATE INDEX IDX_857FE845D6EF0C91 ON node (company_site_id)');
        $this->addSql('CREATE INDEX IDX_857FE8453EB7476B ON node (supply_chain_id)');
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B2243EB7476B');
        $this->addSql('DROP INDEX IDX_CFA5B2243EB7476B ON dpp');
        $this->addSql('ALTER TABLE dpp DROP supply_chain_id, CHANGE company_id company_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE company_site_id company_site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
