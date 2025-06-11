<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240717160835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add qrId.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dpp ADD qr_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE node ADD qr_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD description LONGTEXT DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE step ADD qr_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node DROP qr_id');
        $this->addSql('ALTER TABLE product DROP description');
        $this->addSql('ALTER TABLE dpp DROP qr_id');
        $this->addSql('ALTER TABLE step DROP qr_id');
    }
}
