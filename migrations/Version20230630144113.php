<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230630144113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE band_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE band (id INT NOT NULL, name VARCHAR(255) NOT NULL, origin VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, start INT DEFAULT NULL, split INT DEFAULT NULL, founders VARCHAR(255) DEFAULT NULL, members_count INT DEFAULT NULL, style VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE band_id_seq CASCADE');
        $this->addSql('DROP TABLE band');
    }
}
