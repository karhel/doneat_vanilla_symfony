<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512145012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD address VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ALTER latitude DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ALTER longitude DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal DROP CONSTRAINT fk_9ef68e9cf4a5bd90
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_9ef68e9cf4a5bd90
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal ADD location_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal DROP booked_by_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal DROP booked_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal DROP booked_comment
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal DROP latitude
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal DROP longitude
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal DROP address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal ADD CONSTRAINT FK_9EF68E9C64D218E FOREIGN KEY (location_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9EF68E9C64D218E ON meal (location_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP latitude
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP longitude
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal DROP CONSTRAINT FK_9EF68E9C64D218E
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_9EF68E9C64D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal ADD booked_by_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal ADD booked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal ADD booked_comment TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal ADD latitude DOUBLE PRECISION DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal ADD longitude DOUBLE PRECISION DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal ADD address VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal DROP location_id
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN meal.booked_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal ADD CONSTRAINT fk_9ef68e9cf4a5bd90 FOREIGN KEY (booked_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_9ef68e9cf4a5bd90 ON meal (booked_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD address VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD latitude DOUBLE PRECISION DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD longitude DOUBLE PRECISION DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ALTER latitude SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ALTER longitude SET NOT NULL
        SQL);
    }
}
