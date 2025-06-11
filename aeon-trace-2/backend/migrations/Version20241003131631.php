<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241003131631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add productCompany boolean field to Company.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD product_company TINYINT(1) DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP product_company');
    }
}
