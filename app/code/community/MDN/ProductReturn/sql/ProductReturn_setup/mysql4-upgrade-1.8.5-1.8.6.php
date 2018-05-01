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


$installer->run("

CREATE TABLE  {$this->getTable('rma_reservation')} (
`rr_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`rr_rma_id` INT NOT NULL ,
`rr_product_id` INT NOT NULL ,
`rr_qty` INT NOT NULL ,
INDEX (  `rr_rma_id` ,  `rr_product_id` )
);

");


$installer->endSetup();

