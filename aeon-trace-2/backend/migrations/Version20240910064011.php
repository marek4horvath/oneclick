<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240910064011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add supply chain reference to Dpp.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp ADD supply_chain_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B2245D0D6533 FOREIGN KEY (supply_chain_template_id) REFERENCES supply_chain_template (id)');
        $this->addSql('CREATE INDEX IDX_CFA5B2245D0D6533 ON dpp (supply_chain_template_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B2245D0D6533');
        $this->addSql('DROP INDEX IDX_CFA5B2245D0D6533 ON dpp');
        $this->addSql('ALTER TABLE dpp DROP supply_chain_template_id');
    }
}
