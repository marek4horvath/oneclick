<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240801115802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Joined Table between company and product template.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE company_product_template (company_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_722B89AF979B1AD6 (company_id), INDEX IDX_722B89AFA9F591A7 (product_template_id), PRIMARY KEY(company_id, product_template_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company_product_template ADD CONSTRAINT FK_722B89AF979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_product_template ADD CONSTRAINT FK_722B89AFA9F591A7 FOREIGN KEY (product_template_id) REFERENCES product_template (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_product_template DROP FOREIGN KEY FK_722B89AF979B1AD6');
        $this->addSql('ALTER TABLE company_product_template DROP FOREIGN KEY FK_722B89AFA9F591A7');
        $this->addSql('DROP TABLE company_product_template');
    }
}
