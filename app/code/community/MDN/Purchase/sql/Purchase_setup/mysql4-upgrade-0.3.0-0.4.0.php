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

//define if magento version uses eav model for orders
$tableName = mage::getResourceModel('sales/order')->getTable('sales/order');
$prefix = Mage::getConfig()->getTablePrefix();
$useEavModel = ($tableName == $prefix.'sales_order');

if ($useEavModel)
{
	//rajoute l'attribut payment_validated a la commande
	$installer->addAttribute('order','payment_validated', array(
																'type' 		=> 'int',
																'visible' 	=> true,
																'label'		=> 'Payment status',
																'default'	=> 0,
																'required'  => false,
																'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
																));
	
	$installer->run("
		
		insert into {$this->getTable('sales_order_int')} (entity_type_id, attribute_id, entity_id, value)
		select 11, {$this->getTable('eav_attribute')}.attribute_id, {$this->getTable('sales_order')}.entity_id, 0
		from {$this->getTable('sales_order')}, {$this->getTable('eav_attribute')}
		where {$this->getTable('eav_attribute')}.attribute_code = 'payment_validated'
		and {$this->getTable('sales_order')}.total_paid = 0;
	
		insert into {$this->getTable('sales_order_int')} (entity_type_id, attribute_id, entity_id, value)
		select 11, {$this->getTable('eav_attribute')}.attribute_id, {$this->getTable('sales_order')}.entity_id, 1
		from {$this->getTable('sales_order')}, {$this->getTable('eav_attribute')}
		where {$this->getTable('eav_attribute')}.attribute_code = 'payment_validated'
		and {$this->getTable('sales_order')}.total_paid > 0;
	");															
}
else 
{
	$installer->run("
		ALTER TABLE `{$this->getTable('sales_flat_order')}` ADD `payment_validated` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT  'payment is done';
	
		update {$this->getTable('sales_flat_order')}
		set payment_validated = 0
		where total_paid = 0;
	
		update {$this->getTable('sales_flat_order')}
		set payment_validated = 1
		where total_paid > 0;
	");
}

$installer->endSetup();

?>
