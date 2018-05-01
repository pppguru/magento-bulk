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
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS  {$this->getTable('erp_stock_transfer_products')} (
`stp_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`stp_transfer_id` INT NOT NULL ,
`stp_product_id` INT NOT NULL ,
`stp_qty_requested` INT NOT NULL ,
`stp_qty_transfered` INT NOT NULL ,
`stp_product_sku` VARCHAR(100) NOT NULL ,
`stp_product_name` VARCHAR( 255 ) NOT NULL ,
INDEX (  `stp_transfer_id` ,  `stp_product_id` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
