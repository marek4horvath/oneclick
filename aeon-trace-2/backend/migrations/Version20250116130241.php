<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250116130241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add SupplyChainTemplate reference into ProductTemplate (Product Template having Supply Chain Template reference is Node Template).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_template ADD COLUMN IF NOT EXISTS supply_chain_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product_template ADD CONSTRAINT FK_5CD075145D0D6533 FOREIGN KEY IF NOT EXISTS (supply_chain_template_id) REFERENCES supply_chain_template (id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_5CD075145D0D6533 ON product_template (supply_chain_template_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_template DROP COLUMN IF EXISTS FOREIGN KEY FK_5CD075145D0D6533');
        $this->addSql('DROP INDEX IF EXISTS IDX_5CD075145D0D6533 ON product_template');
        $this->addSql('ALTER TABLE product_template DROP supply_chain_template_id');
    }
}
