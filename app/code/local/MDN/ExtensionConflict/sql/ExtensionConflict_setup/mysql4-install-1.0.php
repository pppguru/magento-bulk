<?php
/**
 * Magento Fianet Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Gr
 * @package    Gr_Fianet
 * @author     Nicolas Fabre <nicolas.fabre@groupereflect.net>
 * @copyright  Copyright (c) 2008 Nicolas Fabre
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();
								

$installer->run("
	
	CREATE TABLE {$this->getTable('extension_conflict')} (
	`ec_id` INT NOT NULL AUTO_INCREMENT ,
	`ec_core_module` VARCHAR( 255 ) NOT NULL ,
	`ec_core_class` VARCHAR( 255 ) NOT NULL ,
	`ec_rewrite_classes` VARCHAR( 255 ) NOT NULL ,
	ec_is_conflict tinyint NOT NULL default 0,
	PRIMARY KEY ( `ec_id` ) 
	) ENGINE = MYISAM;
	
");
																															
$installer->endSetup();

