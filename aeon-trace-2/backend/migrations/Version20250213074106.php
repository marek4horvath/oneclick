<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250213074106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add automaticCalculation for ProductInput and transactions for ProductStep';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE input ADD automatic_calculation TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE product_input ADD unit_measurement VARCHAR(255) DEFAULT \'\', ADD measurement_type VARCHAR(255) DEFAULT \'\', ADD measurement_value DOUBLE PRECISION DEFAULT \'0\', ADD automatic_calculation TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE product_step ADD transactions JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE input DROP automatic_calculation');
        $this->addSql('ALTER TABLE product_step DROP transactions');
        $this->addSql('ALTER TABLE product_input DROP unit_measurement, DROP measurement_type, DROP measurement_value, DROP automatic_calculation');
    }
}
