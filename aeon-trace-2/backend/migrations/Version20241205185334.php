<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241205185334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds dpp connector that manages relation between source and target DPPs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS dpp_connector (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', source_dpp_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', target_dpp_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_A1CCA9489FB59578 (source_dpp_id), INDEX IDX_A1CCA9482040572E (target_dpp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dpp_connector ADD CONSTRAINT FK_A1CCA9489FB59578 FOREIGN KEY IF NOT EXISTS (source_dpp_id) REFERENCES dpp (id)');
        $this->addSql('ALTER TABLE dpp_connector ADD CONSTRAINT FK_A1CCA9482040572E FOREIGN KEY IF NOT EXISTS (target_dpp_id) REFERENCES dpp (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp_connector DROP FOREIGN KEY IF EXISTS FK_A1CCA9489FB59578');
        $this->addSql('ALTER TABLE dpp_connector DROP FOREIGN KEY IF EXISTS FK_A1CCA9482040572E');
        $this->addSql('DROP TABLE IF EXISTS dpp_connector');
    }
}
