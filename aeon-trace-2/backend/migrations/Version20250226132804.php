<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250226132804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add SupplyChainAlgorithm table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE supply_chain_algorithm (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', supply_chain_template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', algorithm JSON NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_63FEE78A5D0D6533 (supply_chain_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supply_chain_algorithm ADD CONSTRAINT FK_63FEE78A5D0D6533 FOREIGN KEY (supply_chain_template_id) REFERENCES supply_chain_template (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE supply_chain_algorithm DROP FOREIGN KEY FK_63FEE78A5D0D6533');
        $this->addSql('DROP TABLE supply_chain_algorithm');
    }
}
