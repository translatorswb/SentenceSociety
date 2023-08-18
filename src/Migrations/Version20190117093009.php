<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190117093009 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE source_phrase_assignment (id INT AUTO_INCREMENT NOT NULL, phrase_id INT NOT NULL, skipped TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_E8E554038671F084 (phrase_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE source_phrase (id INT AUTO_INCREMENT NOT NULL, phrase LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE target_phrase (id INT AUTO_INCREMENT NOT NULL, source_id INT NOT NULL, phrase LONGTEXT NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_AB4101BC953C1C61 (source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rating (id INT AUTO_INCREMENT NOT NULL, target_id INT NOT NULL, value INT NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_D8892622158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE target_phrase_assignment (id INT AUTO_INCREMENT NOT NULL, phrase_id INT NOT NULL, skipped TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_D713DF268671F084 (phrase_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, email VARCHAR(512) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D6495E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE source_phrase_assignment ADD CONSTRAINT FK_E8E554038671F084 FOREIGN KEY (phrase_id) REFERENCES source_phrase (id)');
        $this->addSql('ALTER TABLE target_phrase ADD CONSTRAINT FK_AB4101BC953C1C61 FOREIGN KEY (source_id) REFERENCES source_phrase (id)');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D8892622158E0B66 FOREIGN KEY (target_id) REFERENCES target_phrase (id)');
        $this->addSql('ALTER TABLE target_phrase_assignment ADD CONSTRAINT FK_D713DF268671F084 FOREIGN KEY (phrase_id) REFERENCES target_phrase (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE source_phrase_assignment DROP FOREIGN KEY FK_E8E554038671F084');
        $this->addSql('ALTER TABLE target_phrase DROP FOREIGN KEY FK_AB4101BC953C1C61');
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D8892622158E0B66');
        $this->addSql('ALTER TABLE target_phrase_assignment DROP FOREIGN KEY FK_D713DF268671F084');
        $this->addSql('DROP TABLE source_phrase_assignment');
        $this->addSql('DROP TABLE source_phrase');
        $this->addSql('DROP TABLE target_phrase');
        $this->addSql('DROP TABLE rating');
        $this->addSql('DROP TABLE target_phrase_assignment');
        $this->addSql('DROP TABLE user');
    }
}
