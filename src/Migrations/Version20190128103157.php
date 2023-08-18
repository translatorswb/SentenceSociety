<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190128103157 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE awarded_points (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, amount INT NOT NULL, INDEX IDX_CBEBDE65A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE source_phrase_flag (id INT AUTO_INCREMENT NOT NULL, source_phrase_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_66B4848EEEEF634A (source_phrase_id), INDEX IDX_66B4848EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE target_phrase_flag (id INT AUTO_INCREMENT NOT NULL, target_phrase_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_DDF19B24B2C582A8 (target_phrase_id), INDEX IDX_DDF19B24A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE awarded_points ADD CONSTRAINT FK_CBEBDE65A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE source_phrase_flag ADD CONSTRAINT FK_66B4848EEEEF634A FOREIGN KEY (source_phrase_id) REFERENCES source_phrase (id)');
        $this->addSql('ALTER TABLE source_phrase_flag ADD CONSTRAINT FK_66B4848EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE target_phrase_flag ADD CONSTRAINT FK_DDF19B24B2C582A8 FOREIGN KEY (target_phrase_id) REFERENCES target_phrase (id)');
        $this->addSql('ALTER TABLE target_phrase_flag ADD CONSTRAINT FK_DDF19B24A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE awarded_points');
        $this->addSql('DROP TABLE source_phrase_flag');
        $this->addSql('DROP TABLE target_phrase_flag');
    }
}
