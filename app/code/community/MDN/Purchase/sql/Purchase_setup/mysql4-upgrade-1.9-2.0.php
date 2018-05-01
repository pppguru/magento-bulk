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

$installer->addAttribute('catalog_product','override_subproducts_planning', array(
                        'type'              => 'int',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'Override subproducts planning',
                        'input'             => 'select',
                        'class'             => '',
                        'source'            => 'eav/entity_attribute_source_boolean',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'default'           => '',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false,
                        'apply_to'          => 'bundle,configurable'
						));	

$installer->run("

ALTER TABLE  `{$this->getTable('supply_needs')}` ADD  `sn_is_critical` INT NOT NULL DEFAULT  '0';

ALTER TABLE  `{$this->getTable('purchase_order')}` ADD  `po_missing_price` INT NOT NULL DEFAULT  '0';						
");

//init values for existing orders
$installer->run("
	update {$this->getTable('purchase_order')} set po_missing_price = 0;
	update {$this->getTable('purchase_order')} set po_missing_price = 1
	where po_num in ( select pop_order_num from {$this->getTable('purchase_order_product')} where pop_price_ht = 0 )  ;
	
ALTER TABLE  `{$this->getTable('purchase_order')}` ADD  `po_external_extended_cost` INT NOT NULL DEFAULT  '0';	
ALTER TABLE  `{$this->getTable('purchase_order')}` ADD  `po_data_verified` INT NOT NULL DEFAULT  '0';	

");

$installer->endSetup();
