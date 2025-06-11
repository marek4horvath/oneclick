<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250416101318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mark product inputs as additional, add reference to template inputs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input ADD additional TINYINT(1) DEFAULT 0 NOT NULL, ADD input_reference VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input DROP additional, DROP input_reference');
    }
}
