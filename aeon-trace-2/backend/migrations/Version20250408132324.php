<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250408132324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user relation to product step.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step ADD user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product_step ADD CONSTRAINT FK_CE51DC9DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_CE51DC9DA76ED395 ON product_step (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_step DROP FOREIGN KEY FK_CE51DC9DA76ED395');
        $this->addSql('DROP INDEX IDX_CE51DC9DA76ED395 ON product_step');
        $this->addSql('ALTER TABLE product_step DROP user_id');
    }
}
