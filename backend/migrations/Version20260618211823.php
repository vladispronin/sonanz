<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260618211823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jobs ADD with_metadata BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE tracks ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tracks ALTER job_id TYPE UUID USING job_id::uuid
');
}

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jobs DROP with_metadata');
        $this->addSql('ALTER TABLE tracks DROP status');
        $this->addSql('ALTER TABLE tracks ALTER job_id TYPE VARCHAR(255)');
    }
}
