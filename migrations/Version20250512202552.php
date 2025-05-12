<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512202552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE booking_request (id SERIAL NOT NULL, requested_by_id INT NOT NULL, meal_id INT NOT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, validated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, request_comment TEXT DEFAULT NULL, validation_comment TEXT NOT NULL, closed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6129CABF4DA1E751 ON booking_request (requested_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6129CABF639666D6 ON booking_request (meal_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN booking_request.requested_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN booking_request.validated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN booking_request.closed_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_request ADD CONSTRAINT FK_6129CABF4DA1E751 FOREIGN KEY (requested_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_request ADD CONSTRAINT FK_6129CABF639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_request DROP CONSTRAINT FK_6129CABF4DA1E751
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_request DROP CONSTRAINT FK_6129CABF639666D6
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE booking_request
        SQL);
    }
}
