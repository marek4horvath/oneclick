<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250108054413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the process table and default processes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS process (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, color VARCHAR(7) DEFAULT NULL, process_type VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE logistics ADD IF NOT EXISTS transport_arrival_for_loading DATETIME DEFAULT NULL, ADD IF NOT EXISTS transport_finish_loading DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE step ADD IF NOT EXISTS process_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP IF EXISTS process');
        //$this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C7EC2F574 FOREIGN KEY IF NOT EXISTS (process_id) REFERENCES process (id) ON DELETE CASCADE');
        //$this->addSql('CREATE INDEX IDX_43B9FE3C7EC2F574 ON step (process_id)');

        // Default processes for 'step'
        $this->addDefaultProcess('Raw material collection', '#dab600', 'step');
        $this->addDefaultProcess('Raw material process', '#e9d700', 'step');
        $this->addDefaultProcess('Component', '#008B8B', 'step');
        $this->addDefaultProcess('Packaging material', '#ff5252', 'step');
        $this->addDefaultProcess('Packaging', '#ff7b7b', 'step');
        $this->addDefaultProcess('Oem manufacturing', '#4FFFB0', 'step');
        $this->addDefaultProcess('Oem processing', '#66FF00', 'step');
        $this->addDefaultProcess('Distributor', '#bfff00', 'step');
        $this->addDefaultProcess('Warehouse', '#A020F0', 'step');

        // Default processes for 'node'
        $this->addDefaultProcess('Supplier of raw material', '#dab600', 'node');
        $this->addDefaultProcess('Supplier of processed raw material', '#e9d700', 'node');
        $this->addDefaultProcess('Supplier of components', '#008B8B', 'node');
        $this->addDefaultProcess('Supplier of packaging material', '#ff5252', 'node');
        $this->addDefaultProcess('Supplier of packaging', '#00bfff', 'node');
        $this->addDefaultProcess('Oem manufacturing', '#4FFFB0', 'node');
        $this->addDefaultProcess('Oem assembly', '#66FF00', 'node');
        $this->addDefaultProcess('Oem process', '#4000ff', 'node');
        $this->addDefaultProcess('Distributor', '#bfff00', 'node');
        $this->addDefaultProcess('Warehouse', '#A020F0', 'node');

        $defaultProcessId = '0A2B3368D00511EFB9910242AC120005';
        $this->addSql('UPDATE step SET process_id = UNHEX(\'' . $defaultProcessId . '\') WHERE process_id IS NOT NULL AND process_id NOT IN (SELECT id FROM process)');

        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C7EC2F574 FOREIGN KEY IF NOT EXISTS  (process_id) REFERENCES process (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_43B9FE3C7EC2F574 ON step (process_id)');
    }

    private function addDefaultProcess(string $name, string $color, string $processType): void
    {
        $uuidBinary = $this->generateBinaryUUID();
        $this->addSql("INSERT INTO process (id, name, color, process_type) VALUES ({$uuidBinary}, '{$name}', '{$color}', '{$processType}')");
    }

    private function generateBinaryUUID(): string
    {
        return "UNHEX(REPLACE(UUID(), '-', ''))";
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY FK_43B9FE3C7EC2F574');
        $this->addSql('DROP IF EXISTS TABLE process');
        $this->addSql('ALTER TABLE logistics DROP IF EXISTS transport_arrival_for_loading, DROP IF EXISTS transport_finish_loading');
        $this->addSql('DROP IF EXISTS INDEX IDX_43B9FE3C7EC2F574 ON step');
        $this->addSql('ALTER TABLE step ADD IF NOT EXISTS process VARCHAR(255) NOT NULL, DROP IF EXISTS process_id');
    }
}
