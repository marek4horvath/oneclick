<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241203085203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Changes the column name departure_dime to departure_time in the logistics table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics CHANGE departure_dime departure_time DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics CHANGE departure_time departure_dime DATETIME DEFAULT NULL');
    }
}
