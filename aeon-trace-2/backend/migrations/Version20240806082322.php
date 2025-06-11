<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240806082322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add lat and lng to product.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD latitude DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD longitude DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP latitude, DROP longitude');
    }
}
