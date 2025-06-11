<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240718115857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add phone for user entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE phone phone VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE phone phone VARCHAR(255) DEFAULT \'\'');
    }
}
