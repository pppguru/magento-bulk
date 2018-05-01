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


$installer->addAttribute('catalog_product','manual_supply_need_date', array(
                        'type'              => 'text',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'Manual supply need date',
                        'input'             => 'text',
                        'class'             => '',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => false,
                        'required'          => false,
                        'user_defined'      => false,
                        'default'           => '',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false
						));	

$installer->run("						
	ALTER TABLE  `{$this->getTable('supply_needs')}`  ADD  `sn_priority` BIGINT NOT NULL DEFAULT  '0';						
");
	
$installer->endSetup();
