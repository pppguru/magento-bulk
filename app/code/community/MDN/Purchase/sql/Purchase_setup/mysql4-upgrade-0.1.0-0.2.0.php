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
			
//rajoute l'attribut default_supply_delay au produit
$installer->addAttribute('catalog_product','default_supply_delay', array(
                                    'type' 		=> 'int',
                                    'visible' 	=> false,
                                    'label'		=> 'Default Supply delay',
                                    'required'  => false,
                                    'default'   => '5',
                                    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                                    'note'		=> 'In days'
                                    ));
						
//rajoute l'attribut supply_date au produit (prochaine date d'approvisionnement)
$installer->addAttribute('catalog_product','supply_date', array(
															'type' 		=> 'datetime',
															'visible' 	=> false,
															'label'		=> 'Supply date',
															'required'  => false,
															'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
															));

																																											
$installer->endSetup();

?>