<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240910165646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add qr folder id to supply chain template.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE supply_chain_template ADD qr_folder_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE supply_chain_template DROP qr_folder_id');
    }
}
