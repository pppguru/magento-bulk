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
	
	//rajoute l'attribut default_supply_delay au produit
	$installer->addAttribute('order','fullstock_date', array(
																'type' 		=> 'datetime',
																'visible' 	=> true,
																'label'		=> 'Full Stock date',
																'required'  => false,
																'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
																));
			
									
	//rajoute l'attribut default_supply_delay au produit
	$installer->addAttribute('order','estimated_shipping_date', array(
																'type' 		=> 'datetime',
																'visible' 	=> true,
																'label'		=> 'Estimated Shipping Date',
																'required'  => false,
																'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
																));
}
else 
{
	$installer->run("
		
		ALTER TABLE `{$this->getTable('sales_flat_order')}` 
		ADD `fullstock_date` DATETIME NULL COMMENT  'full stock date for order',
		ADD estimated_shipping_date DATETIME NULL COMMENT  'estimated date of shipping';
		
	");
		
}

$installer->endSetup();

?>
