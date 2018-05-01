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

CREATE TABLE  {$this->getTable('rma_supplier_return')} (
`rsr_id` INT NOT NULL AUTO_INCREMENT ,
`rsr_supplier_id` INT NOT NULL ,
`rsr_increment_id` VARCHAR( 20 ) NOT NULL ,
`rsr_status` VARCHAR( 25 ) NOT NULL ,
PRIMARY KEY (  `rsr_id` ) ,
INDEX (  `rsr_supplier_id` ,  `rsr_increment_id` ,  `rsr_status` )
);

");

$installer->endSetup();
