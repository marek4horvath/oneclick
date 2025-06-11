<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240716073108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add long a lat to company.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD latitude DOUBLE PRECISION DEFAULT \'0\', ADD longitude DOUBLE PRECISION DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP latitude, DROP longitude');
    }
}
