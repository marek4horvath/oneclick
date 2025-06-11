<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240730084750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove step_has_dpp table and change company - product relation to many to many.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_company (product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', company_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_9E6612FF4584665A (product_id), INDEX IDX_9E6612FF979B1AD6 (company_id), PRIMARY KEY(product_id, company_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_product (company_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_F3181E7A979B1AD6 (company_id), INDEX IDX_F3181E7A4584665A (product_id), PRIMARY KEY(company_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_company ADD CONSTRAINT FK_9E6612FF4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_company ADD CONSTRAINT FK_9E6612FF979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_product ADD CONSTRAINT FK_F3181E7A979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_product ADD CONSTRAINT FK_F3181E7A4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE step_has_dpp DROP FOREIGN KEY FK_4B9869314584665A');
        $this->addSql('ALTER TABLE step_has_dpp DROP FOREIGN KEY FK_4B98693173B21E9C');
        $this->addSql('ALTER TABLE step_has_dpp DROP FOREIGN KEY FK_4B986931294AB09E');
        $this->addSql('DROP TABLE step_has_dpp');
        $this->addSql('ALTER TABLE node ADD company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD company_site_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD qr_image VARCHAR(255) DEFAULT \'\', ADD create_qr TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845D6EF0C91 FOREIGN KEY (company_site_id) REFERENCES company_site (id)');
        $this->addSql('CREATE INDEX IDX_857FE845979B1AD6 ON node (company_id)');
        $this->addSql('CREATE INDEX IDX_857FE845D6EF0C91 ON node (company_site_id)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD979B1AD6');
        $this->addSql('DROP INDEX IDX_D34A04AD979B1AD6 ON product');
        $this->addSql('ALTER TABLE product DROP company_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE step_has_dpp (id INT AUTO_INCREMENT NOT NULL, step_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', dpp_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_4B986931294AB09E (dpp_id), INDEX IDX_4B9869314584665A (product_id), INDEX IDX_4B98693173B21E9C (step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE step_has_dpp ADD CONSTRAINT FK_4B9869314584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE step_has_dpp ADD CONSTRAINT FK_4B98693173B21E9C FOREIGN KEY (step_id) REFERENCES step (id)');
        $this->addSql('ALTER TABLE step_has_dpp ADD CONSTRAINT FK_4B986931294AB09E FOREIGN KEY (dpp_id) REFERENCES dpp (id)');
        $this->addSql('ALTER TABLE product_company DROP FOREIGN KEY FK_9E6612FF4584665A');
        $this->addSql('ALTER TABLE product_company DROP FOREIGN KEY FK_9E6612FF979B1AD6');
        $this->addSql('ALTER TABLE company_product DROP FOREIGN KEY FK_F3181E7A979B1AD6');
        $this->addSql('ALTER TABLE company_product DROP FOREIGN KEY FK_F3181E7A4584665A');
        $this->addSql('DROP TABLE product_company');
        $this->addSql('DROP TABLE company_product');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845979B1AD6');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845D6EF0C91');
        $this->addSql('DROP INDEX IDX_857FE845979B1AD6 ON node');
        $this->addSql('DROP INDEX IDX_857FE845D6EF0C91 ON node');
        $this->addSql('ALTER TABLE node DROP company_id, DROP company_site_id, DROP qr_image, DROP create_qr');
        $this->addSql('ALTER TABLE product ADD company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD979B1AD6 ON product (company_id)');
    }
}
