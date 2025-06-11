<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250410123324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add collection of product steps to product step.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step ADD used_in_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product_step ADD CONSTRAINT FK_CE51DC9DF6352C9F FOREIGN KEY (used_in_id) REFERENCES product_step (id)');
        $this->addSql('CREATE INDEX IDX_CE51DC9DF6352C9F ON product_step (used_in_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step DROP FOREIGN KEY FK_CE51DC9DF6352C9F');
        $this->addSql('DROP INDEX IDX_CE51DC9DF6352C9F ON product_step');
        $this->addSql('ALTER TABLE product_step DROP used_in_id');
    }
}
