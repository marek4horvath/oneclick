<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240730092229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Split Product to template and actual product.';
    }

    public function up(Schema $schema): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        // Check if the foreign key constraints exist before dropping
        $tables = $schemaManager->listTables();
        foreach ($tables as $table) {
            if ($table->getName() === 'product') {
                $foreignKeys = $table->getForeignKeys();
                foreach ($foreignKeys as $fk) {
                    if ($fk->getName() === 'FK_D34A04ADD6EF0C91') {
                        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADD6EF0C91');
                    }
                    if ($fk->getName() === 'FK_D34A04AD49CCB89A') {
                        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD49CCB89A');
                    }
                    if ($fk->getName() === 'FK_D34A04AD979B1AD6') {
                        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD979B1AD6');
                    }
                }
            }
        }

        // Apply schema changes
        $this->addSql('CREATE TABLE product_input (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            product_step_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            dpp_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            name VARCHAR(255) DEFAULT \'\' NOT NULL,
            text_value VARCHAR(255) DEFAULT NULL,
            text_area_value LONGTEXT DEFAULT NULL,
            document VARCHAR(255) DEFAULT NULL,
            image VARCHAR(255) DEFAULT NULL,
            INDEX IDX_7974498BA26B052C (product_step_id),
            INDEX IDX_7974498B294AB09E (dpp_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE product_input_image (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            input_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            image VARCHAR(255) DEFAULT \'\' NOT NULL,
            INDEX IDX_D1EC52F436421AD6 (input_id),
            INDEX IDX_D1EC52F4C53D045F (image),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE product_step (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            step_template_reference_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            parent_step_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            product_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            name VARCHAR(255) NOT NULL,
            step_image VARCHAR(255) DEFAULT \'\',
            sort INT NOT NULL,
            qr_image VARCHAR(255) DEFAULT \'\',
            create_qr TINYINT(1) DEFAULT 0 NOT NULL,
            INDEX IDX_CE51DC9D808D3D72 (step_template_reference_id),
            INDEX IDX_CE51DC9D1FA6ADA (parent_step_id),
            INDEX IDX_CE51DC9D4584665A (product_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE product_template (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            steps_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            name VARCHAR(255) NOT NULL,
            product_image VARCHAR(255) DEFAULT \'\',
            description LONGTEXT DEFAULT \'\' NOT NULL,
            INDEX IDX_5CD07514979B1AD6 (company_id),
            INDEX IDX_5CD0751449CCB89A (steps_template_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE product_input ADD CONSTRAINT FK_7974498BA26B052C FOREIGN KEY (product_step_id) REFERENCES product_step (id)');
        $this->addSql('ALTER TABLE product_input ADD CONSTRAINT FK_7974498B294AB09E FOREIGN KEY (dpp_id) REFERENCES dpp (id)');
        $this->addSql('ALTER TABLE product_input_image ADD CONSTRAINT FK_D1EC52F436421AD6 FOREIGN KEY (input_id) REFERENCES product_input (id)');
        $this->addSql('ALTER TABLE product_step ADD CONSTRAINT FK_CE51DC9D808D3D72 FOREIGN KEY (step_template_reference_id) REFERENCES step (id)');
        $this->addSql('ALTER TABLE product_step ADD CONSTRAINT FK_CE51DC9D1FA6ADA FOREIGN KEY (parent_step_id) REFERENCES product_step (id)');
        $this->addSql('ALTER TABLE product_step ADD CONSTRAINT FK_CE51DC9D4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_template ADD CONSTRAINT FK_5CD07514979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_template ADD CONSTRAINT FK_5CD0751449CCB89A FOREIGN KEY (steps_template_id) REFERENCES steps_template (id)');
        $this->addSql('ALTER TABLE dpp_input DROP FOREIGN KEY IF EXISTS FK_446CFA55294AB09E');
        $this->addSql('ALTER TABLE dpp_input_image DROP FOREIGN KEY IF EXISTS FK_17A154DC36421AD6');
        $this->addSql('DROP TABLE IF EXISTS dpp_input');
        $this->addSql('DROP TABLE IF EXISTS dpp_input_image');
        $this->addSql('ALTER TABLE dpp ADD product_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD locked TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE dpp ADD CONSTRAINT FK_CFA5B2244584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CFA5B2244584665A ON dpp (product_id)');

        // Check if columns exist before attempting to drop
        $columns = $schemaManager->listTableColumns('product');
        $columnNames = array_map(fn ($column) => $column->getName(), $columns);

        if (in_array('company_id', $columnNames)) {
            $this->addSql('ALTER TABLE product DROP FOREIGN KEY IF EXISTS FK_D34A04ADD6EF0C91');
            $this->addSql('DROP INDEX IF EXISTS IDX_D34A04ADD6EF0C91 ON product');
            $this->addSql('ALTER TABLE product ADD company_site_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD address_street VARCHAR(255) DEFAULT \'\', ADD address_city VARCHAR(255) DEFAULT \'\', ADD address_postcode VARCHAR(255) DEFAULT \'\', ADD address_house_no VARCHAR(255) DEFAULT \'\', ADD address_country VARCHAR(255) DEFAULT \'\', DROP company_id, DROP steps_template_id');
            $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADD6EF0C91 FOREIGN KEY (company_site_id) REFERENCES company_site (id)');
            $this->addSql('CREATE INDEX IDX_D34A04ADD6EF0C91 ON product (company_site_id)');
        } else {
            // If the column does not exist, log or handle accordingly
            $this->addSql('ALTER TABLE product ADD company_site_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD address_street VARCHAR(255) DEFAULT \'\', ADD address_city VARCHAR(255) DEFAULT \'\', ADD address_postcode VARCHAR(255) DEFAULT \'\', ADD address_house_no VARCHAR(255) DEFAULT \'\', ADD address_country VARCHAR(255) DEFAULT \'\', DROP steps_template_id');
            $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADD6EF0C91 FOREIGN KEY (company_site_id) REFERENCES company_site (id)');
            $this->addSql('CREATE INDEX IDX_D34A04ADD6EF0C91 ON product (company_site_id)');
        }

        $this->addSql('ALTER TABLE product_attribute DROP FOREIGN KEY IF EXISTS FK_94DA59764584665A');
        $this->addSql('ALTER TABLE product_attribute ADD CONSTRAINT FK_94DA59764584665A FOREIGN KEY (product_id) REFERENCES product_template (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_attribute DROP FOREIGN KEY FK_94DA59764584665A');

        $this->addSql('CREATE TABLE dpp_input (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            dpp_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
            text_value VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
            text_area_value LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
            document VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
            image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
            INDEX IDX_446CFA55294AB09E (dpp_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE dpp_input_image (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            input_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' COLLATE `utf8mb4_unicode_ci`,
            INDEX IDX_17A154DCC53D045F (image),
            INDEX IDX_17A154DC36421AD6 (input_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE dpp_input ADD CONSTRAINT FK_446CFA55294AB09E FOREIGN KEY (dpp_id) REFERENCES dpp (id)');
        $this->addSql('ALTER TABLE dpp_input_image ADD CONSTRAINT FK_17A154DC36421AD6 FOREIGN KEY (input_id) REFERENCES dpp_input (id)');
        $this->addSql('DROP TABLE product_input');
        $this->addSql('DROP TABLE product_input_image');
        $this->addSql('DROP TABLE product_step');
        $this->addSql('DROP TABLE product_template');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADD6EF0C91');
        $this->addSql('DROP INDEX IDX_D34A04ADD6EF0C91 ON product');
        $this->addSql('ALTER TABLE product ADD steps_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP address_street, DROP address_city, DROP address_postcode, DROP address_house_no, DROP address_country, CHANGE company_site_id company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD49CCB89A FOREIGN KEY (steps_template_id) REFERENCES steps_template (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD979B1AD6 ON product (company_id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD49CCB89A ON product (steps_template_id)');
        $this->addSql('ALTER TABLE product_attribute DROP FOREIGN KEY FK_94DA59764584665A');
        $this->addSql('ALTER TABLE product_attribute ADD CONSTRAINT FK_94DA59764584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE dpp DROP FOREIGN KEY FK_CFA5B2244584665A');
        $this->addSql('DROP INDEX UNIQ_CFA5B2244584665A ON dpp');
        $this->addSql('ALTER TABLE dpp DROP product_id, DROP locked');
    }
}
