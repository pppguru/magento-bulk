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

//Rajoute les champs pour la gestion du cache
$installer->run("
	ALTER TABLE `{$this->getTable('order_to_prepare_pending')}` ADD `opp_shipto_name` varchar(255);
	ALTER TABLE `{$this->getTable('order_to_prepare_pending')}` ADD `opp_remain_to_ship` TEXT;
	ALTER TABLE `{$this->getTable('order_to_prepare_pending')}` ADD `opp_details` TEXT;
	ALTER TABLE `{$this->getTable('order_to_prepare_pending')}` ADD `opp_order_increment_id` varchar(30);
	
	ALTER TABLE `{$this->getTable('order_to_prepare')}` ADD `details` TEXT;
");
																																											
$installer->endSetup();

?>