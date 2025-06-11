<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240822094218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add company and product template to step.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step ADD company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD product_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3CA9F591A7 FOREIGN KEY (product_template_id) REFERENCES product_template (id)');
        $this->addSql('CREATE INDEX IDX_43B9FE3C979B1AD6 ON step (company_id)');
        $this->addSql('CREATE INDEX IDX_43B9FE3CA9F591A7 ON step (product_template_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY FK_43B9FE3C979B1AD6');
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY FK_43B9FE3CA9F591A7');
        $this->addSql('DROP INDEX IDX_43B9FE3C979B1AD6 ON step');
        $this->addSql('DROP INDEX IDX_43B9FE3CA9F591A7 ON step');
        $this->addSql('ALTER TABLE step DROP company_id, DROP product_template_id');
    }
}
