<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();


//Create tables
$installer->run("
	
CREATE TABLE  {$this->getTable('rma')} (
 `rma_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `rma_ref` VARCHAR( 25 ) NOT NULL ,
 `rma_created_at` DATETIME NULL ,
 `rma_updated_at` DATETIME NULL ,
 `rma_order_id` INT NOT NULL ,
 `rma_customer_phone` VARCHAR( 25 ) NOT NULL ,
 `rma_address_id` INT NOT NULL ,
 `rma_customer_id` INT NOT NULL ,
 `rma_customer_name` VARCHAR( 50 ) NOT NULL ,
 `rma_customer_email` VARCHAR( 255 ) NOT NULL ,
 `rma_status` VARCHAR( 35 ) NOT NULL ,
 `rma_reason` VARCHAR( 20 ) NOT NULL ,
 `rma_expire_date` DATE NULL ,
 `rma_public_description` TEXT NULL ,
 `rma_private_description` TEXT NULL ,
 `rma_aftersale_description` TEXT NULL ,
 `rma_action_order_id` INT NULL ,
 `rma_carrier` VARCHAR( 25 ) NULL ,
 `rma_shipping_cost` DECIMAL( 8, 2 ) NULL ,
 `rma_technical_cost` DECIMAL( 8, 2 ) NULL ,
 `rma_reception_date` DATE NULL ,
 `rma_return_date` DATE NULL ,
INDEX (  `rma_order_id` ,  `rma_customer_id` )
);

CREATE TABLE  {$this->getTable('rma_products')} (
 `rp_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `rp_rma_id` INT NOT NULL ,
 `rp_product_id` INT NOT NULL ,
 `rp_orderitem_id` INT NOT NULL ,
 `rp_qty` INT NOT NULL ,
 `rp_product_name` VARCHAR( 255 ) NOT NULL ,
 `rp_description` TEXT NULL ,
INDEX (  `rp_rma_id` )
);


INSERT INTO  {$this->getTable('cms_block')} 
(`block_id`, `title`, `identifier`, `content`, `creation_time`, `update_time`, `is_active`) VALUES 
(NULL, 'cgv_rma', 'cgv_rma','Please fill Product return commitment from admin panel CMS > Static blocks > cgv_rma block', NOW(), NOW(), '1');

INSERT INTO {$this->getTable('cms_block_store')} (`block_id` ,`store_id`)
SELECT `block_id`,'0' FROM {$this->getTable('cms_block')} WHERE identifier = 'cgv_rma';
");


$installer->endSetup();

