<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241002095232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Qr and TimeStamp fields to logistics.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics ADD qr_id INT DEFAULT NULL, ADD qr_image VARCHAR(255) DEFAULT \'\', ADD tsa_verified_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE product_input ADD logistics_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product_input ADD CONSTRAINT FK_7974498B7D418FFA FOREIGN KEY (logistics_id) REFERENCES logistics (id)');
        $this->addSql('CREATE INDEX IDX_7974498B7D418FFA ON product_input (logistics_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_input DROP FOREIGN KEY FK_7974498B7D418FFA');
        $this->addSql('DROP INDEX IDX_7974498B7D418FFA ON product_input');
        $this->addSql('ALTER TABLE product_input DROP logistics_id');
        $this->addSql('ALTER TABLE logistics DROP qr_id, DROP qr_image, DROP tsa_verified_at');
    }
}
