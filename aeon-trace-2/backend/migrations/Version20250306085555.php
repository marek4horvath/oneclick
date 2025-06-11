<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250306085555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow transaction to be null on product step';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step CHANGE transactions transactions JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('UPDATE product_step SET transactions = null WHERE transactions = \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step CHANGE transactions transactions JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('UPDATE product_step SET transactions = \'\' WHERE transactions IS NULL');
    }
}
