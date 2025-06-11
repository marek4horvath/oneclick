<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250519141510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add updatable field to database table of dpps and product steps.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP VIEW IF EXISTS dpp_listing_view');
        $this->addSql('
        CREATE VIEW dpp_listing_view AS (
            SELECT 
                \'Dpp\' AS type,
                dpp.id,
                dpp.name,
                dpp.qr_image,
                dpp.ongoing_dpp,
                dpp.state,
                dpp.node_id,
                dpp.created_at,
                dpp.tsa_verified_at,
                dpp.timestamp_path,
                dpp.updatable,
                (
                    SELECT COUNT(*)
                    FROM product_input pi
                    INNER JOIN product_step ps ON pi.product_step_id = ps.id
                    WHERE ps.dpp_id = dpp.id
                ) AS number_of_inputs,
                dpp.user_id,
                u.first_name AS user_first_name,
                u.last_name AS user_last_name
            FROM dpp
            LEFT JOIN user u ON dpp.user_id = u.id
            UNION ALL
            SELECT 
                \'ProductStep\' AS type,
                product_step.id,
                product_step.dpp_name,
                product_step.qr_image,
                product_step.ongoing_dpp,
                product_step.state,
                product_step.node_id,
                product_step.created_at,
                product_step.tsa_verified_at,
                product_step.timestamp_path,
                product_step.updatable,
                (
                    SELECT COUNT(*)
                    FROM product_input pi
                    WHERE pi.product_step_id = product_step.id
                ) AS number_of_inputs,
                product_step.user_id,
                u2.first_name AS user_first_name,
                u2.last_name AS user_last_name
            FROM product_step
            LEFT JOIN dpp ON product_step.dpp_id = dpp.id
            LEFT JOIN user u2 ON product_step.user_id = u2.id
            GROUP BY product_step.id         
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW IF EXISTS dpp_listing_view');
        $this->addSql('
        CREATE VIEW dpp_listing_view AS (
            SELECT 
                \'Dpp\' AS type,
                dpp.id,
                dpp.name,
                dpp.qr_image,
                dpp.ongoing_dpp,
                dpp.state,
                dpp.node_id,
                dpp.created_at,
                dpp.tsa_verified_at,
                dpp.timestamp_path,
                (
                    SELECT COUNT(*)
                    FROM product_input pi
                    INNER JOIN product_step ps ON pi.product_step_id = ps.id
                    WHERE ps.dpp_id = dpp.id
                ) AS number_of_inputs,
                dpp.user_id,
                u.first_name AS user_first_name,
                u.last_name AS user_last_name
            FROM dpp
            LEFT JOIN user u ON dpp.user_id = u.id
            UNION ALL
            SELECT 
                \'ProductStep\' AS type,
                product_step.id,
                product_step.dpp_name,
                product_step.qr_image,
                product_step.ongoing_dpp,
                product_step.state,
                product_step.node_id,
                product_step.created_at,
                product_step.tsa_verified_at,
                product_step.timestamp_path,
                (
                    SELECT COUNT(*)
                    FROM product_input pi
                    WHERE pi.product_step_id = product_step.id
                ) AS number_of_inputs,
                product_step.user_id,
                u2.first_name AS user_first_name,
                u2.last_name AS user_last_name
            FROM product_step
            LEFT JOIN dpp ON product_step.dpp_id = dpp.id
            LEFT JOIN user u2 ON product_step.user_id = u2.id
            GROUP BY product_step.id         
        )');
    }
}
