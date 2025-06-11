<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240712083220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add all migrations into one.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE attribute (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_site (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) DEFAULT \'\' NOT NULL, latitude DOUBLE PRECISION DEFAULT \'0\' NOT NULL, longitude DOUBLE PRECISION DEFAULT \'0\' NOT NULL, address_street VARCHAR(255) DEFAULT \'\', address_city VARCHAR(255) DEFAULT \'\', address_postcode VARCHAR(255) DEFAULT \'\', address_house_no VARCHAR(255) DEFAULT \'\', address_country VARCHAR(255) DEFAULT \'\', INDEX IDX_2A2E130A979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_site_image (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', company_site_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', image VARCHAR(255) DEFAULT \'\', INDEX IDX_BCE93DCCD6EF0C91 (company_site_id), INDEX IDX_BCE93DCCC53D045F (image), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dpp (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', company_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', company_site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, cover_image VARCHAR(255) DEFAULT \'\', qr_image VARCHAR(255) DEFAULT \'\', create_qr TINYINT(1) DEFAULT 0 NOT NULL, numerical_value NUMERIC(8, 2) DEFAULT NULL, numerical_value_unit VARCHAR(25) DEFAULT \'\' NOT NULL, description LONGTEXT DEFAULT \'\', INDEX IDX_CFA5B224979B1AD6 (company_id), INDEX IDX_CFA5B224D6EF0C91 (company_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dpp_image (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', dpp_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', image VARCHAR(255) DEFAULT \'\', INDEX IDX_5979CCDD294AB09E (dpp_id), INDEX IDX_5979CCDDC53D045F (image), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dpp_video (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', dpp_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', video VARCHAR(255) DEFAULT \'\', INDEX IDX_E08312AE294AB09E (dpp_id), INDEX IDX_E08312AE7CC7DA2C (video), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', steps_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, INDEX IDX_D34A04AD979B1AD6 (company_id), INDEX IDX_D34A04AD49CCB89A (steps_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_attribute (id INT AUTO_INCREMENT NOT NULL, product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', attribute_id INT NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_94DA59764584665A (product_id), INDEX IDX_94DA5976B6E62EFA (attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE step (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', steps_template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', parent_step_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, sort INT NOT NULL, qr_image VARCHAR(255) DEFAULT \'\', create_qr TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_43B9FE3C49CCB89A (steps_template_id), INDEX IDX_43B9FE3C1FA6ADA (parent_step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE step_has_dpp (id INT AUTO_INCREMENT NOT NULL, step_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', dpp_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_4B98693173B21E9C (step_id), INDEX IDX_4B986931294AB09E (dpp_id), INDEX IDX_4B9869314584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE steps_template (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(255) DEFAULT \'\' NOT NULL, first_name VARCHAR(255) DEFAULT \'\' NOT NULL, last_name VARCHAR(255) DEFAULT \'\' NOT NULL, password VARCHAR(255) DEFAULT \'\' NOT NULL, invitation_token VARCHAR(255) DEFAULT \'\' NOT NULL, active TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', roles LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', discr VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT \'\', company_logo VARCHAR(255) DEFAULT \'\', phone VARCHAR(255) DEFAULT \'\', description LONGTEXT DEFAULT \'\', address_street VARCHAR(255) DEFAULT \'\', address_city VARCHAR(255) DEFAULT \'\', address_postcode VARCHAR(255) DEFAULT \'\', address_house_no VARCHAR(255) DEFAULT \'\', address_country VARCHAR(255) DEFAULT \'\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_token (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', token_owner_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', token VARCHAR(255) NOT NULL, INDEX IDX_BDF55A63A63BC7A (token_owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company_site ADD CONSTRAINT FK_2A2E130A979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE company_site_image ADD CONSTRAINT FK_BCE93DCCD6EF0C91 FOREIGN KEY (company_site_id) REFERENCES company_site (id)');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B224979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B224D6EF0C91 FOREIGN KEY (company_site_id) REFERENCES company_site (id)');
        $this->addSql('ALTER TABLE dpp_image ADD CONSTRAINT FK_5979CCDD294AB09E FOREIGN KEY (dpp_id) REFERENCES dpp (id)');
        $this->addSql('ALTER TABLE dpp_video ADD CONSTRAINT FK_E08312AE294AB09E FOREIGN KEY (dpp_id) REFERENCES dpp (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD49CCB89A FOREIGN KEY (steps_template_id) REFERENCES steps_template (id)');
        $this->addSql('ALTER TABLE product_attribute ADD CONSTRAINT FK_94DA59764584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_attribute ADD CONSTRAINT FK_94DA5976B6E62EFA FOREIGN KEY (attribute_id) REFERENCES attribute (id)');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C49CCB89A FOREIGN KEY (steps_template_id) REFERENCES steps_template (id)');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C1FA6ADA FOREIGN KEY (parent_step_id) REFERENCES step (id)');
        $this->addSql('ALTER TABLE step_has_dpp ADD CONSTRAINT FK_4B98693173B21E9C FOREIGN KEY (step_id) REFERENCES step (id)');
        $this->addSql('ALTER TABLE step_has_dpp ADD CONSTRAINT FK_4B986931294AB09E FOREIGN KEY (dpp_id) REFERENCES dpp (id)');
        $this->addSql('ALTER TABLE step_has_dpp ADD CONSTRAINT FK_4B9869314584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_token ADD CONSTRAINT FK_BDF55A63A63BC7A FOREIGN KEY (token_owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_site DROP FOREIGN KEY FK_2A2E130A979B1AD6');
        $this->addSql('ALTER TABLE company_site_image DROP FOREIGN KEY FK_BCE93DCCD6EF0C91');
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B224979B1AD6');
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B224D6EF0C91');
        $this->addSql('ALTER TABLE dpp_image DROP FOREIGN KEY FK_5979CCDD294AB09E');
        $this->addSql('ALTER TABLE dpp_video DROP FOREIGN KEY FK_E08312AE294AB09E');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD979B1AD6');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD49CCB89A');
        $this->addSql('ALTER TABLE product_attribute DROP FOREIGN KEY FK_94DA59764584665A');
        $this->addSql('ALTER TABLE product_attribute DROP FOREIGN KEY FK_94DA5976B6E62EFA');
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY FK_43B9FE3C49CCB89A');
        $this->addSql('ALTER TABLE step DROP FOREIGN KEY FK_43B9FE3C1FA6ADA');
        $this->addSql('ALTER TABLE step_has_dpp DROP FOREIGN KEY FK_4B98693173B21E9C');
        $this->addSql('ALTER TABLE step_has_dpp DROP FOREIGN KEY FK_4B986931294AB09E');
        $this->addSql('ALTER TABLE step_has_dpp DROP FOREIGN KEY FK_4B9869314584665A');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649979B1AD6');
        $this->addSql('ALTER TABLE user_token DROP FOREIGN KEY FK_BDF55A63A63BC7A');
        $this->addSql('DROP TABLE attribute');
        $this->addSql('DROP TABLE company_site');
        $this->addSql('DROP TABLE company_site_image');
        $this->addSql('DROP TABLE dpp');
        $this->addSql('DROP TABLE dpp_image');
        $this->addSql('DROP TABLE dpp_video');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_attribute');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE step');
        $this->addSql('DROP TABLE step_has_dpp');
        $this->addSql('DROP TABLE steps_template');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_token');
    }
}
