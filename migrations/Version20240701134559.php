<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701134559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order_products` (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', related_order_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', product_id VARCHAR(255) NOT NULL, quantity INT NOT NULL, price DOUBLE PRECISION NOT NULL, weight DOUBLE PRECISION NOT NULL, INDEX IDX_5242B8EB2B1C2395 (related_order_id), INDEX IDX_5242B8EB4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `orders` (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', client_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `products` (id VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, weight DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order_products` ADD CONSTRAINT FK_5242B8EB2B1C2395 FOREIGN KEY (related_order_id) REFERENCES `orders` (id)');
        $this->addSql('ALTER TABLE `order_products` ADD CONSTRAINT FK_5242B8EB4584665A FOREIGN KEY (product_id) REFERENCES `products` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order_products` DROP FOREIGN KEY FK_5242B8EB2B1C2395');
        $this->addSql('ALTER TABLE `order_products` DROP FOREIGN KEY FK_5242B8EB4584665A');
        $this->addSql('DROP TABLE `order_products`');
        $this->addSql('DROP TABLE `orders`');
        $this->addSql('DROP TABLE `products`');
    }
}
