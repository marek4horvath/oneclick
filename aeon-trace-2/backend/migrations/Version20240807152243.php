<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240807152243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add quantity to step.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step ADD quantity INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step DROP quantity');
    }
}
