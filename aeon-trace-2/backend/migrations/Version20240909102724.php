<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240909102724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add create Empty Dpp boolean';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B2243EB7476B');
        $this->addSql('DROP TABLE supply_chain');
        $this->addSql('DROP INDEX IDX_CFA5B2243EB7476B ON dpp');
        $this->addSql('ALTER TABLE dpp ADD create_empty_dpp TINYINT(1) DEFAULT 0 NOT NULL, DROP supply_chain_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE supply_chain (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE dpp ADD supply_chain_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP create_empty_dpp');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B2243EB7476B FOREIGN KEY (supply_chain_id) REFERENCES supply_chain (id)');
        $this->addSql('CREATE INDEX IDX_CFA5B2243EB7476B ON dpp (supply_chain_id)');
    }
}
