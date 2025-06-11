<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250108102933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Modifies the length of the "color" column in the "process" table to allow up to 9 characters, ensuring compatibility with longer color codes like rgba or similar.';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE process CHANGE color color VARCHAR(9) DEFAULT \'#ffffff\'');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('ALTER TABLE process CHANGE color color VARCHAR(7) DEFAULT \'#ffffff\'');
    }
}
