<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250317124309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created at field for entities that are displayed in tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_site ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE input ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE input_category ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE logistics_template ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE process ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE product ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE product_input ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE product_template ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE supply_chain_algorithm ADD name VARCHAR(255) NOT NULL, ADD inputs JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE supply_chain_template ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE process DROP created_at');
        $this->addSql('ALTER TABLE input_category DROP created_at');
        $this->addSql('ALTER TABLE logistics_template DROP created_at');
        $this->addSql('ALTER TABLE supply_chain_template DROP created_at');
        $this->addSql('ALTER TABLE product_template DROP created_at');
        $this->addSql('ALTER TABLE input DROP created_at');
        $this->addSql('ALTER TABLE product_input DROP created_at');
        $this->addSql('ALTER TABLE product DROP created_at');
        $this->addSql('ALTER TABLE company_site DROP created_at');
        $this->addSql('ALTER TABLE supply_chain_algorithm DROP name, DROP inputs');
    }
}
