<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241030101230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to update logistics and input tables: adds foreign keys, new fields, and modifies existing structure.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE input ADD IF NOT EXISTS logistics_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE input ADD CONSTRAINT FK_D82832D7A7C218ED FOREIGN KEY IF NOT EXISTS (logistics_template_id) REFERENCES logistics_template (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_D82832D7A7C218ED ON input (logistics_template_id)');
        $this->addSql('ALTER TABLE logistics ADD IF NOT EXISTS logistics_parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD IF NOT EXISTS type_of_transport VARCHAR(255) DEFAULT NULL, ADD IF NOT EXISTS starting_point_lat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD IF NOT EXISTS starting_point_lng DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD IF NOT EXISTS destination_point_lat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD IF NOT EXISTS destination_point_lng DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD IF NOT EXISTS total_distance DOUBLE PRECISION DEFAULT \'0\', ADD IF NOT EXISTS number_of_steps INT NOT NULL, ADD IF NOT EXISTS departure_dime DATETIME DEFAULT NULL, ADD IF NOT EXISTS arrival_time DATETIME DEFAULT NULL, ADD IF NOT EXISTS transport_arrival_for_loading DATETIME DEFAULT NULL, ADD IF NOT EXISTS transport_finish_loading DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE logistics ADD CONSTRAINT FK_AE8ABD697E50523C FOREIGN KEY IF NOT EXISTS (logistics_parent_id) REFERENCES logistics (id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_AE8ABD697E50523C ON logistics (logistics_parent_id)');
        $this->addSql('ALTER TABLE logistics_template DROP FOREIGN KEY IF EXISTS FK_BE36B4849CCB89A');
        $this->addSql('ALTER TABLE logistics_template DROP FOREIGN KEY IF EXISTS FK_BE36B48979B1AD6');
        $this->addSql('DROP INDEX IF EXISTS IDX_BE36B4849CCB89A ON logistics_template');
        $this->addSql('DROP INDEX IF EXISTS IDX_BE36B48979B1AD6 ON logistics_template');
        $this->addSql('ALTER TABLE logistics_template ADD IF NOT EXISTS type_of_transport_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD IF NOT EXISTS latitude DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD IF NOT EXISTS longitude DOUBLE PRECISION DEFAULT \'0\' NOT NULL, DROP IF EXISTS company_id, DROP IF EXISTS steps_template_id');
        $this->addSql('ALTER TABLE logistics_template ADD CONSTRAINT FK_BE36B488E683066 FOREIGN KEY IF NOT EXISTS (type_of_transport_id) REFERENCES transport_type (id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_BE36B488E683066 ON logistics_template (type_of_transport_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics DROP FOREIGN KEY FK_AE8ABD697E50523C');
        $this->addSql('DROP INDEX IDX_AE8ABD697E50523C ON logistics');
        $this->addSql('ALTER TABLE logistics DROP logistics_parent_id, DROP type_of_transport, DROP starting_point_lat, DROP starting_point_lng, DROP destination_point_lat, DROP destination_point_lng, DROP total_distance, DROP number_of_steps, DROP departure_dime, DROP arrival_time, DROP transport_arrival_for_loading, DROP transport_finish_loading');
        $this->addSql('ALTER TABLE logistics_template DROP FOREIGN KEY FK_BE36B488E683066');
        $this->addSql('DROP INDEX IDX_BE36B488E683066 ON logistics_template');
        $this->addSql('ALTER TABLE logistics_template ADD steps_template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP latitude, DROP longitude, CHANGE type_of_transport_id company_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE logistics_template ADD CONSTRAINT FK_BE36B4849CCB89A FOREIGN KEY (steps_template_id) REFERENCES steps_template (id)');
        $this->addSql('ALTER TABLE logistics_template ADD CONSTRAINT FK_BE36B48979B1AD6 FOREIGN KEY (company_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_BE36B4849CCB89A ON logistics_template (steps_template_id)');
        $this->addSql('CREATE INDEX IDX_BE36B48979B1AD6 ON logistics_template (company_id)');
        $this->addSql('ALTER TABLE input DROP FOREIGN KEY FK_D82832D7A7C218ED');
        $this->addSql('DROP INDEX IF EXISTS IDX_D82832D7A7C218ED ON input');
        $this->addSql('ALTER TABLE input DROP logistics_template_id');
    }
}
