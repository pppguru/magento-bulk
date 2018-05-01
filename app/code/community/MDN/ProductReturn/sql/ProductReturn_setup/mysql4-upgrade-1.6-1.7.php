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
ALTER TABLE {$this->getTable('rma')} CHANGE `rma_reason` `rma_reason` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

CREATE TABLE {$this->getTable('rma_history')} (
`rh_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`rh_rma_id` INT( 11 ) NOT NULL ,
`rh_date` DATETIME NOT NULL ,
`rh_comments` VARCHAR( 255 ) NOT NULL
);

");

$installer->endSetup();
