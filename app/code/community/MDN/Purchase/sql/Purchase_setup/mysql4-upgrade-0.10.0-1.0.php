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
																						
//Cree la table pour les taux de tva produit
$installer->run("
	CREATE TABLE IF NOT EXISTS `{$this->getTable('purchase_tva_rates')}` (
	  `ptr_id` int(11) NOT NULL auto_increment,
	  `ptr_name` varchar(25) NOT NULL,
	  `ptr_value` decimal(6,2) NOT NULL,
	  PRIMARY KEY  (`ptr_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

//Insere les taux par d�faut
$installer->run("
	INSERT INTO `{$this->getTable('purchase_tva_rates')}` (`ptr_id` ,`ptr_name` ,`ptr_value`)
	VALUES 
	(null, 'No tax', '0'),
    (null, 'VAT 5%', '5'),
	(null, 'VAT 5.5%', '5.5'),
    (null, 'VAT 7%', '7'), 
    (null, 'VAT 10%', '10'),
	(null, 'VAT 19.6%', '19.6'),
    (null, 'VAT 20%', '20'),
    (null, 'VAT 21%', '21');
");

																																											
$installer->endSetup();
