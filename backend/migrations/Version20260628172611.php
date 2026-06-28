<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260628172611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE albums ALTER job_id TYPE UUID USING job_id::uuid');
        $this->addSql('ALTER TABLE jobs ADD user_id UUID NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE albums ALTER job_id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE jobs DROP user_id');
    }
}
