<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240820142130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add longitude and latitude columns.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input ADD latitude_value DOUBLE PRECISION DEFAULT \'0\', ADD longitude_value DOUBLE PRECISION DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input DROP latitude_value, DROP longitude_value');
    }
}
