<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240911125001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove unused "name" field from ProductInput';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input DROP name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input ADD name VARCHAR(255) DEFAULT \'\' NOT NULL');
    }
}
