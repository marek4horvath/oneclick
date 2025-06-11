<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250117081209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to remove the step_parents_step_children table, modify the logistics table by removing certain columns (transport_arrival_for_loading, transport_finish_loading), and add new fields (unit_measurement, measurement_type, measurement_value) to the product_step table. Additionally, this migration modifies the type of the process_id column in the step table to UUID format (BINARY(16)).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics DROP IF EXISTS transport_arrival_for_loading, DROP IF EXISTS transport_finish_loading');
        $this->addSql('ALTER TABLE product_step ADD IF NOT EXISTS unit_measurement VARCHAR(255) DEFAULT \'\', ADD IF NOT EXISTS measurement_type VARCHAR(255) DEFAULT \'\', ADD IF NOT EXISTS measurement_value DOUBLE PRECISION DEFAULT \'0\'');
        $this->addSql('ALTER TABLE step CHANGE process_id process_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('ALTER TABLE logistics ADD IF NOT EXISTS transport_arrival_for_loading DATETIME DEFAULT NULL, ADD IF NOT EXISTS transport_finish_loading DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE step CHANGE process_id process_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product_step DROP IF EXISTS unit_measurement, DROP IF EXISTS measurement_type, DROP IF EXISTS measurement_value');
    }
}
