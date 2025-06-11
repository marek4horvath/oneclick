<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250529075539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nullable starting_company_name and destination_company_name columns to the logistics table';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE logistics ADD starting_company_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE logistics ADD destination_company_name VARCHAR(255) DEFAULT NULL');

    }

    public function down(Schema $schema): void
    {

        $this->addSql('ALTER TABLE logistics DROP starting_company_name');
        $this->addSql('ALTER TABLE logistics DROP destination_company_name');

    }
}
