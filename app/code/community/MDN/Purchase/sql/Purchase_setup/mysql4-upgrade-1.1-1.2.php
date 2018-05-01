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
			
//Rajoute la colonne taux tva dans les lignes produits d'une commande
$installer->run("

ALTER TABLE `{$this->getTable('purchase_order_product')}` ADD `pop_tax_rate` DECIMAL( 6, 2 ) NOT NULL ;

");

//Copie les taux de tva des commandes déja existantes dans les lignes produit
$installer->run("
	update {$this->getTable('purchase_order_product')}, {$this->getTable('purchase_order')}
	set pop_tax_rate = po_tax_rate
	where po_num = pop_order_num;
");


$installer->endSetup();
