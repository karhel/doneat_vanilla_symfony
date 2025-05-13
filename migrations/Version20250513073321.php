<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250513073321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP city
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP postalcode
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP country
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP number
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP road
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_request ADD status INT NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_request DROP status
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD city VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD postalcode VARCHAR(8) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD country VARCHAR(100) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD number VARCHAR(5) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD road VARCHAR(255) DEFAULT NULL
        SQL);
    }
}
