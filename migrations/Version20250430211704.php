<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430211704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE meal (id SERIAL NOT NULL, created_by_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9EF68E9CB03A8386 ON meal (created_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN meal.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE meal_meal_tag (meal_id INT NOT NULL, meal_tag_id INT NOT NULL, PRIMARY KEY(meal_id, meal_tag_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_639C25E5639666D6 ON meal_meal_tag (meal_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_639C25E5D5D8DE18 ON meal_meal_tag (meal_tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE meal_tag (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, is_allergen BOOLEAN NOT NULL, icon VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal ADD CONSTRAINT FK_9EF68E9CB03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal_meal_tag ADD CONSTRAINT FK_639C25E5639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal_meal_tag ADD CONSTRAINT FK_639C25E5D5D8DE18 FOREIGN KEY (meal_tag_id) REFERENCES meal_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal DROP CONSTRAINT FK_9EF68E9CB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal_meal_tag DROP CONSTRAINT FK_639C25E5639666D6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE meal_meal_tag DROP CONSTRAINT FK_639C25E5D5D8DE18
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE meal
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE meal_meal_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE meal_tag
        SQL);
    }
}
