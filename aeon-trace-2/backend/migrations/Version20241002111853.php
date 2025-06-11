<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241002111853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add createdAt to logistics';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics DROP created_at');
    }
}
