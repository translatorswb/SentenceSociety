<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190128222716 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE awarded_points ADD timestamp DATETIME NOT NULL');
        $this->addSql('ALTER TABLE target_phrase ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE target_phrase ADD CONSTRAINT FK_AB4101BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AB4101BCA76ED395 ON target_phrase (user_id)');
        $this->addSql('ALTER TABLE rating ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D8892622A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D8892622A76ED395 ON rating (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE awarded_points DROP timestamp');
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D8892622A76ED395');
        $this->addSql('DROP INDEX IDX_D8892622A76ED395 ON rating');
        $this->addSql('ALTER TABLE rating DROP user_id');
        $this->addSql('ALTER TABLE target_phrase DROP FOREIGN KEY FK_AB4101BCA76ED395');
        $this->addSql('DROP INDEX IDX_AB4101BCA76ED395 ON target_phrase');
        $this->addSql('ALTER TABLE target_phrase DROP user_id');
    }
}
