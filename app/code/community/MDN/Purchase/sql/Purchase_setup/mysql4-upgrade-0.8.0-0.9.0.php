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
																						
//Rajoute un champs au produit pour les exclure des besoins d'appro
$installer->run("
ALTER TABLE `{$this->getTable('catalog_product_entity')}` ADD `exclude_from_supply_needs` TINYINT NOT NULL DEFAULT 0;
");

//D�clare la colonne comme attribut (static) pour produit
$installer->addAttribute('catalog_product','exclude_from_supply_needs', array(
                        'type'              => 'static',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'exclude_from_supply_needs',
                        'input'             => 'text',
                        'class'             => '',
                        'source'            => '',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                        'visible'           => false,
                        'required'          => true,
                        'user_defined'      => false,
                        'default'           => '0',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'visible_in_advanced_search' => false,
                        'unique'            => false
															));
																																											
$installer->endSetup();
