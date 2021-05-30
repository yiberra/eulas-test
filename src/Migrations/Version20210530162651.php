<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210530162651 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {

        $this->addSql("CREATE TABLE `product` (
            `id` bigint(15) NOT NULL,
            `styleNumber` varchar(255) NOT NULL COMMENT 'Code of product',
            `name` varchar(255) NOT NULL COMMENT 'Name of product',
            `price_amount` int(10) NOT NULL COMMENT 'Amount of product price',
            `price_currency` varchar(5) DEFAULT NULL COMMENT 'Currency of product price',
            `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array of product images',
            `toSync` tinyint(1) NOT NULL COMMENT 'Prepared to sync into CSV'
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->addSql("ALTER TABLE `product`
        ADD PRIMARY KEY (`id`),
        ADD UNIQUE KEY `styleNumber` (`styleNumber`)");

        $this->addSql("ALTER TABLE `product`
        MODIFY `id` bigint(15) NOT NULL AUTO_INCREMENT");

    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DROP TABLE `product`");
    }
}
