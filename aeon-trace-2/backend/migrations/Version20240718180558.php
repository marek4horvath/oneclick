<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240718180558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make Steps Template non required.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step CHANGE steps_template_id steps_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step CHANGE steps_template_id steps_template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
