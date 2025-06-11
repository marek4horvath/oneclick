<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024064907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete fieldy entity LogisticsTemplate.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics_template DROP IF EXISTS starting_point_lat, DROP IF EXISTS starting_point_lng, DROP IF EXISTS destination_point_lat, DROP IF EXISTS destination_point_lng, DROP IF EXISTS total_distance, DROP IF EXISTS type_of_transport');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics_template ADD starting_point_lat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD starting_point_lng DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD destination_point_lat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD destination_point_lng DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD total_distance DOUBLE PRECISION DEFAULT \'0\', ADD type_of_transport VARCHAR(255) DEFAULT NULL');
    }
}
