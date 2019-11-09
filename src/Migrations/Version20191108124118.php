<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191108124118 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, username VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_794381C67294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C67294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article ADD user_created VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455A76ED395');
        $this->addSql('DROP INDEX IDX_C7440455A76ED395 ON client');
        $this->addSql('ALTER TABLE client DROP user_id');
        $this->addSql('ALTER TABLE user ADD client_id INT DEFAULT NULL, ADD login_name VARCHAR(255) NOT NULL, DROP fullname, DROP birthdate, DROP address1, DROP address2, DROP city, DROP state, DROP postcode');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64919EB6921 ON user (client_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE review');
        $this->addSql('ALTER TABLE article DROP user_created');
        $this->addSql('ALTER TABLE client ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_C7440455A76ED395 ON client (user_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64919EB6921');
        $this->addSql('DROP INDEX UNIQ_8D93D64919EB6921 ON user');
        $this->addSql('ALTER TABLE user ADD birthdate DATE NOT NULL, ADD address1 VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD address2 VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD city VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD state VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD postcode VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, DROP client_id, CHANGE login_name fullname VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
