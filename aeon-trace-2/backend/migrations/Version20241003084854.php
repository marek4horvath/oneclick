<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241003084854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move type of transport to product step.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics DROP type_of_transport');
        $this->addSql('ALTER TABLE product_step ADD type_of_transport VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step DROP type_of_transport');
        $this->addSql('ALTER TABLE logistics ADD type_of_transport VARCHAR(255) DEFAULT NULL');
    }
}
