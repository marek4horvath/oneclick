<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240718103637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image for product';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD product_image VARCHAR(255) DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP product_image');
    }
}
