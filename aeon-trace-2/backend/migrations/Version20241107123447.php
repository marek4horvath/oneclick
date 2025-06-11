<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241107123447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create starting_point table to store coordinates as a separate entity. Establish a many-to-many relationship between logistics and starting_point via the logistics_starting_points join table. Drop the starting_point_lat and starting_point_lng fields from logistics.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS logistics_starting_points (logistics_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', starting_point_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_193FC8AF7D418FFA (logistics_id), INDEX IDX_193FC8AFF39C0FE7 (starting_point_id), PRIMARY KEY(logistics_id, starting_point_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS starting_point (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', latitude DOUBLE PRECISION DEFAULT \'0\' NOT NULL, longitude DOUBLE PRECISION DEFAULT \'0\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE logistics_starting_points ADD CONSTRAINT FK_193FC8AF7D418FFA FOREIGN KEY IF NOT EXISTS (logistics_id) REFERENCES logistics (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE logistics_starting_points ADD CONSTRAINT FK_193FC8AFF39C0FE7 FOREIGN KEY IF NOT EXISTS (starting_point_id) REFERENCES starting_point (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE logistics DROP COLUMN IF EXISTS starting_point_lat, DROP COLUMN IF EXISTS starting_point_lng');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE logistics_starting_points DROP FOREIGN KEY IF EXISTS FK_193FC8AF7D418FFA');
        $this->addSql('ALTER TABLE logistics_starting_points DROP FOREIGN KEY IF EXISTS FK_193FC8AFF39C0FE7');
        $this->addSql('DROP TABLE IF EXISTS logistics_starting_points');
        $this->addSql('DROP TABLE IF EXISTS starting_point');
        $this->addSql('ALTER TABLE logistics ADD starting_point_lat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD starting_point_lng DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
