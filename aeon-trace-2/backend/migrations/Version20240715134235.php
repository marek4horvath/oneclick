<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240715134235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Node and SupplyChain tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE node (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', company_site_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', supply_chain_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', qr_image VARCHAR(255) DEFAULT \'\', create_qr TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, description LONGTEXT DEFAULT \'\' NOT NULL, UNIQUE INDEX UNIQ_857FE845727ACA70 (parent_id), INDEX IDX_857FE845979B1AD6 (company_id), INDEX IDX_857FE845D6EF0C91 (company_site_id), INDEX IDX_857FE8453EB7476B (supply_chain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supply_chain (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845727ACA70 FOREIGN KEY (parent_id) REFERENCES node (id)');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845D6EF0C91 FOREIGN KEY (company_site_id) REFERENCES company_site (id)');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE8453EB7476B FOREIGN KEY (supply_chain_id) REFERENCES supply_chain (id)');
        $this->addSql('ALTER TABLE dpp ADD node_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP numerical_value, DROP numerical_value_unit');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B224460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id)');
        $this->addSql('CREATE INDEX IDX_CFA5B224460D9FD7 ON dpp (node_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B224460D9FD7');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845727ACA70');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845979B1AD6');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845D6EF0C91');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE8453EB7476B');
        $this->addSql('DROP TABLE node');
        $this->addSql('DROP TABLE supply_chain');
        $this->addSql('DROP INDEX IDX_CFA5B224460D9FD7 ON dpp');
        $this->addSql('ALTER TABLE dpp ADD numerical_value NUMERIC(8, 2) DEFAULT NULL, ADD numerical_value_unit VARCHAR(25) DEFAULT \'\' NOT NULL, DROP node_id');
    }
}
