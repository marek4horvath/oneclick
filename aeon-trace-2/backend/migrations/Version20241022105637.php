<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241022105637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added fieldy starting_point_lat, starting_point_lng, destination_point_lat, destination_point_lng, destination_point_lng and total_distance to logistics_template';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics_template ADD starting_point_lat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD starting_point_lng DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD destination_point_lat DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD destination_point_lng DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD total_distance DOUBLE PRECISION DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logistics_template DROP starting_point_lat, DROP starting_point_lng, DROP destination_point_lat, DROP destination_point_lng, DROP total_distance');
    }
}
