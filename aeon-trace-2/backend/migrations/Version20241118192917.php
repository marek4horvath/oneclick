<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241118192917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change relations between Dpp - Product, SupplyChain - ProductTemplate and ProductTemplate - Node';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE node_product_template (node_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_1F52EE5A460D9FD7 (node_id), INDEX IDX_1F52EE5AA9F591A7 (product_template_id), PRIMARY KEY(node_id, product_template_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supply_chain_template_product_template (supply_chain_template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_A4467DB05D0D6533 (supply_chain_template_id), INDEX IDX_A4467DB0A9F591A7 (product_template_id), PRIMARY KEY(supply_chain_template_id, product_template_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE node_product_template ADD CONSTRAINT FK_1F52EE5A460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node_product_template ADD CONSTRAINT FK_1F52EE5AA9F591A7 FOREIGN KEY (product_template_id) REFERENCES product_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supply_chain_template_product_template ADD CONSTRAINT FK_A4467DB05D0D6533 FOREIGN KEY (supply_chain_template_id) REFERENCES supply_chain_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supply_chain_template_product_template ADD CONSTRAINT FK_A4467DB0A9F591A7 FOREIGN KEY (product_template_id) REFERENCES product_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B2244584665A');
        $this->addSql('DROP INDEX UNIQ_CFA5B2244584665A ON dpp');
        $this->addSql('ALTER TABLE dpp DROP product_id');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845A9F591A7');
        $this->addSql('DROP INDEX IDX_857FE845A9F591A7 ON node');
        $this->addSql('ALTER TABLE node DROP product_template_id');
        $this->addSql('ALTER TABLE product ADD dpp_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD294AB09E FOREIGN KEY (dpp_id) REFERENCES dpp (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD294AB09E ON product (dpp_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node_product_template DROP FOREIGN KEY FK_1F52EE5A460D9FD7');
        $this->addSql('ALTER TABLE node_product_template DROP FOREIGN KEY FK_1F52EE5AA9F591A7');
        $this->addSql('ALTER TABLE supply_chain_template_product_template DROP FOREIGN KEY FK_A4467DB05D0D6533');
        $this->addSql('ALTER TABLE supply_chain_template_product_template DROP FOREIGN KEY FK_A4467DB0A9F591A7');
        $this->addSql('DROP TABLE node_product_template');
        $this->addSql('DROP TABLE supply_chain_template_product_template');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD294AB09E');
        $this->addSql('DROP INDEX IDX_D34A04AD294AB09E ON product');
        $this->addSql('ALTER TABLE product DROP dpp_id');
        $this->addSql('ALTER TABLE node ADD product_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845A9F591A7 FOREIGN KEY (product_template_id) REFERENCES product_template (id)');
        $this->addSql('CREATE INDEX IDX_857FE845A9F591A7 ON node (product_template_id)');
        $this->addSql('ALTER TABLE dpp ADD product_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B2244584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CFA5B2244584665A ON dpp (product_id)');
    }
}
