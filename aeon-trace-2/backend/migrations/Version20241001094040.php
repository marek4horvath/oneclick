<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241001094040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move type of transport away from product inputs and assign it to logistics.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics ADD type_of_transport VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_input DROP type_of_transport');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input ADD type_of_transport VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE logistics DROP type_of_transport');
    }
}
