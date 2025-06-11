<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241111103340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dpp - Logistics relation - ManyToMany';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS dpp_logistics (dpp_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', logistics_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_1BCA514F294AB09E (dpp_id), INDEX IDX_1BCA514F7D418FFA (logistics_id), PRIMARY KEY(dpp_id, logistics_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dpp_logistics ADD CONSTRAINT FK_1BCA514F294AB09E FOREIGN KEY IF NOT EXISTS (dpp_id) REFERENCES dpp (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dpp_logistics ADD CONSTRAINT FK_1BCA514F7D418FFA FOREIGN KEY IF NOT EXISTS (logistics_id) REFERENCES logistics (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY IF EXISTS FK_CFA5B224C4100549');
        $this->addSql('DROP INDEX IF EXISTS IDX_CFA5B224C4100549 ON dpp');
        $this->addSql('ALTER TABLE dpp DROP IF EXISTS materials_received_from_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp_logistics DROP FOREIGN KEY IF EXISTS FK_1BCA514F294AB09E');
        $this->addSql('ALTER TABLE dpp_logistics DROP FOREIGN KEY IF EXISTS FK_1BCA514F7D418FFA');
        $this->addSql('DROP TABLE IF EXISTS dpp_logistics');
        $this->addSql('ALTER TABLE dpp ADD IF NOT EXISTS materials_received_from_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B224C4100549 FOREIGN KEY IF NOT EXISTS (materials_received_from_id) REFERENCES logistics (id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_CFA5B224C4100549 ON dpp (materials_received_from_id)');
    }
}
