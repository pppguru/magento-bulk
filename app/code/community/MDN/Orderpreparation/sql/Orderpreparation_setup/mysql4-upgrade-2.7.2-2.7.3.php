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

//update indexes
$installer->run("	

ALTER TABLE  {$this->getTable('order_to_prepare_item')} 
ADD  `user` INT NOT NULL ,
ADD  `preparation_warehouse` INT NOT NULL;

ALTER TABLE  {$this->getTable('order_to_prepare')}
ADD UNIQUE  `idx_unique_order_user_warehouse` (  `order_id` ,  `user` ,  `preparation_warehouse` );

");
	
$installer->endSetup();
