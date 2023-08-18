<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190128145528 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE source_phrase_assignment');
        $this->addSql('DROP TABLE target_phrase_assignment');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE source_phrase_assignment (id INT AUTO_INCREMENT NOT NULL, phrase_id INT NOT NULL, skipped TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_E8E554038671F084 (phrase_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE target_phrase_assignment (id INT AUTO_INCREMENT NOT NULL, phrase_id INT NOT NULL, skipped TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_D713DF268671F084 (phrase_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE source_phrase_assignment ADD CONSTRAINT FK_E8E554038671F084 FOREIGN KEY (phrase_id) REFERENCES source_phrase (id)');
        $this->addSql('ALTER TABLE target_phrase_assignment ADD CONSTRAINT FK_D713DF268671F084 FOREIGN KEY (phrase_id) REFERENCES target_phrase (id)');
    }
}
