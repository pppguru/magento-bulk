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

//rajoute l'attribut ordered_qty au produit
$installer->addAttribute('catalog_product','ordered_qty', array(
                                                                'type' 		=> 'int',
                                                                'visible' 	=> false,
                                                                'label'		=> 'Ordered Qty',
                                                                'required'  => false,
                                                                'default'   => '0',
                                                                'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                                                                'note'		=> 'Qty included in pending orders'
                                                                ));
															
//rajoute l'attribut reserved_qty au produit
$installer->addAttribute('catalog_product','reserved_qty', array(
                                                                'type' 		=> 'int',
                                                                'visible' 	=> false,
                                                                'label'		=> 'Reserved Qty',
                                                                'required'  => false,
                                                                'default'   => '0',
                                                                'global'    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                                                                ));

if ($useEavModel)
{
	$installer->run("
		ALTER TABLE `{$this->getTable('sales_order')}` ADD `stocks_updated` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT  'Check that ERP has considered order';
	");
}
else 
{
	$installer->run("
		ALTER TABLE `{$this->getTable('sales_flat_order')}` ADD `stocks_updated` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT  'Check that ERP has considered order';
	");
}

//Creation des tables specifiques pour les achats
$installer->run("


ALTER TABLE `{$this->getTable('sales_flat_order_item')}` 
ADD `comments` VARCHAR( 255 ) NULL ,
ADD `reserved_qty` INT NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS {$this->getTable('stock_movement')} (
  sm_id bigint(20) NOT NULL auto_increment,
  sm_product_id int(11) NOT NULL,
  sm_qty int(11) NOT NULL,
  sm_coef int(11) NOT NULL,
  sm_description varchar(255) NOT NULL,
  sm_type varchar(20) NOT NULL,
  sm_date date NOT NULL,
  sm_estimated_date date default NULL,
  sm_ui varchar(20) default NULL COMMENT 'Unique identifier',
  sm_po_num int(11) default NULL,
  PRIMARY KEY  (sm_id),
  UNIQUE KEY sm_ui (sm_ui),
  KEY sm_product_id (sm_product_id),
  KEY sm_po_num (sm_po_num)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('purchase_contact')};
CREATE TABLE IF NOT EXISTS {$this->getTable('purchase_contact')} (
  pc_num int(11) NOT NULL auto_increment,
  pc_lastname varchar(50) NOT NULL,
  pc_firstname varchar(50) default NULL,
  pc_function varchar(50) default NULL,
  pc_phone varchar(20) default NULL,
  pc_fax varchar(20) default NULL,
  pc_mobile varchar(20) default NULL,
  pc_email varchar(255) default NULL,
  pc_comments text,
  pc_type varchar(15) default NULL,
  pc_entity_id int(11) NOT NULL,
  pc_country varchar(3) default NULL,
  PRIMARY KEY  (pc_num),
  KEY pc_entity_id (pc_entity_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'purchase_manufacturer'
-- 

DROP TABLE IF EXISTS {$this->getTable('purchase_manufacturer')};
CREATE TABLE IF NOT EXISTS {$this->getTable('purchase_manufacturer')} (
  man_id int(11) NOT NULL auto_increment,
  man_name varchar(255) NOT NULL,
  man_contact varchar(255) default NULL,
  man_address1 varchar(255) default NULL,
  man_address2 varchar(255) default NULL,
  man_zipcode varchar(50) default NULL,
  man_country varchar(3) default NULL,
  man_tel varchar(20) default NULL,
  man_fax varchar(20) default NULL,
  man_email varchar(255) default NULL,
  man_website varchar(255) default NULL,
  man_comments text,
  man_city varchar(255) default NULL,
  man_mediabox_id varchar(20) default NULL,
  man_attribute_option_id int(11) default NULL,
  PRIMARY KEY  (man_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'purchase_manufacturer_supplier'
-- 

DROP TABLE IF EXISTS {$this->getTable('purchase_manufacturer_supplier')};
CREATE TABLE IF NOT EXISTS {$this->getTable('purchase_manufacturer_supplier')} (
  pms_num int(11) NOT NULL auto_increment,
  pms_supplier_id int(11) NOT NULL,
  pms_manufacturer_id int(11) NOT NULL,
  pms_official tinyint(4) NOT NULL DEFAULT 0,
  pms_price_position varchar(20) NOT NULL,
  pms_gamme varchar(50) NOT NULL,
  PRIMARY KEY  (pms_num),
  KEY pms_supplier_id (pms_supplier_id,pms_manufacturer_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'purchase_order'
-- 

DROP TABLE IF EXISTS {$this->getTable('purchase_order')};
CREATE TABLE IF NOT EXISTS {$this->getTable('purchase_order')} (
  po_num int(11) NOT NULL auto_increment,
  po_sup_num int(11) NOT NULL,
  po_date date NOT NULL,
  po_order_id varchar(20) NOT NULL,
  po_supply_date date NULL,
  po_carrier varchar(20) NOT NULL,
  po_payment_type varchar(20) NOT NULL,
  po_currency varchar(5) NOT NULL,
  po_invoice_date date NULL,
  po_invoice_ref varchar(50) DEFAULT ' ',
  po_paid tinyint(4) NOT NULL DEFAULT 0,
  po_sent tinyint(4) NOT NULL DEFAULT 0,
  po_currency_change_rate decimal(6,4) NOT NULL DEFAULT '1.0000',
  po_shipping_cost decimal(10,2) NOT NULL,
  po_shipping_cost_base decimal(10,2) NOT NULL,
  po_zoll_cost decimal(10,2) NOT NULL,
  po_zoll_cost_base decimal(10,2) NOT NULL,
  po_finished tinyint(4) NOT NULL DEFAULT 0,
  po_tax_rate decimal(5,2) NOT NULL DEFAULT '0.00',
  po_mediabox_num varchar(10) NOT NULL,
  po_comments text,
  po_payment_date date default NULL,
  PRIMARY KEY  (po_num),
  KEY po_sup_num (po_sup_num)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'purchase_order_product'
-- 

DROP TABLE IF EXISTS {$this->getTable('purchase_order_product')};
CREATE TABLE IF NOT EXISTS {$this->getTable('purchase_order_product')} (
  pop_num int(11) NOT NULL auto_increment,
  pop_order_num int(11) NOT NULL,
  pop_product_id int(11) default NULL,
  pop_product_name varchar(255) NOT NULL,
  pop_qty int(11) NOT NULL,
  pop_supplied_qty int(11) NOT NULL DEFAULT 0,
  pop_price_ht decimal(8,4) NOT NULL,
  pop_price_ht_base decimal(8,4) NOT NULL,
  pop_supplier_ref varchar(25) default NULL,
  pop_eco_tax decimal(8,3) NOT NULL default '0.000',
  pop_eco_tax_base decimal(8,3) NOT NULL default '0.000',
  pop_extended_costs decimal(8,3) NOT NULL default '0.000',
  pop_extended_costs_base decimal(8,3) NOT NULL default '0.000',
  PRIMARY KEY  (pop_num),
  KEY pop_order_num (pop_order_num)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'purchase_product_manufacturer'
-- 

DROP TABLE IF EXISTS {$this->getTable('purchase_product_manufacturer')};
CREATE TABLE IF NOT EXISTS {$this->getTable('purchase_product_manufacturer')} (
  ppm_id int(11) NOT NULL auto_increment,
  ppm_product_id int(11) NOT NULL,
  ppm_manufacturer_num int(11) NOT NULL,
  ppm_comments text NOT NULL,
  ppm_reference varchar(50) default NULL,
  PRIMARY KEY  (ppm_id),
  KEY ppm_product_id (ppm_product_id,ppm_manufacturer_num)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'purchase_product_supplier'
-- 

DROP TABLE IF EXISTS {$this->getTable('purchase_product_supplier')};
CREATE TABLE IF NOT EXISTS {$this->getTable('purchase_product_supplier')} (
  pps_num int(11) NOT NULL auto_increment,
  pps_product_id int(11) NOT NULL,
  pps_supplier_num int(11) NOT NULL,
  pps_comments text NOT NULL,
  pps_reference varchar(50) default NULL,
  pps_price_position varchar(20) default NULL,
  pps_last_price decimal(10,2) NOT NULL default '0.00',
  pps_last_order_date date default NULL,
  PRIMARY KEY  (pps_num),
  KEY pps_product_id (pps_product_id,pps_supplier_num)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'purchase_supplier'
-- 

DROP TABLE IF EXISTS {$this->getTable('purchase_supplier')};
CREATE TABLE IF NOT EXISTS {$this->getTable('purchase_supplier')} (
  sup_id int(11) NOT NULL auto_increment,
  sup_name varchar(255) NOT NULL,
  sup_address1 varchar(255) default NULL,
  sup_address2 varchar(255) default NULL,
  sup_zipcode varchar(20) default NULL,
  sup_country varchar(3) default NULL,
  sup_city varchar(255) default NULL,
  sup_tel varchar(255) default NULL,
  sup_fax varchar(20) default NULL,
  sup_contact varchar(255) default NULL,
  sup_mail varchar(255) default NULL,
  sup_website varchar(255) default NULL,
  sup_sale_online tinyint(1) default '0',
  sup_account_login varchar(50) default NULL,
  sup_account_password varchar(50) default NULL,
  sup_order_mini varchar(255) default NULL,
  sup_supply_delay varchar(255) default NULL,
  sup_supply_delay_max varchar(255) default NULL,
  sup_carrier varchar(50) default NULL,
  sup_comments text,
  sup_rma_tel varchar(255) default NULL,
  sup_rma_mail varchar(255) default NULL,
  sup_rma_comments text,
  sup_mediabox_id varchar(20) default NULL,
  PRIMARY KEY  (sup_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Script de creation des mouvements de stock a partir des stocks des produits
-- 

-- Suppression des mouvements de stock
delete from {$this->getTable('stock_movement')};

-- Creation des stocks movement bases sur les stocks produit
Insert into {$this->getTable('stock_movement')} (sm_product_id, sm_qty, sm_coef, sm_description, sm_type, sm_date)
Select product_id, qty, 1, 'Initial stock', 'supply', now() from {$this->getTable('cataloginventory_stock_item')};

");

if ($useEavModel)
{
	$installer->run("
	
		-- Tag  1 les commandes completes
		update {$this->getTable('sales_order')} 
		set stocks_updated = 1
		where entity_id in
		(
			select entity_id
			from {$this->getTable('sales_order_varchar')}, {$this->getTable('eav_attribute')}, {$this->getTable('eav_entity_type')}
			where {$this->getTable('eav_attribute')}.entity_type_id = {$this->getTable('eav_entity_type')}.entity_type_id
			and {$this->getTable('sales_order_varchar')}.attribute_id = {$this->getTable('eav_attribute')}.attribute_id
			and {$this->getTable('eav_entity_type')}.entity_type_code = 'order'
			and {$this->getTable('eav_attribute')}.attribute_code = 'status'
			and (
		{$this->getTable('sales_order_varchar')}.value = 'complete' or {$this->getTable('sales_order_varchar')}.value = 'canceled' or {$this->getTable('sales_order_varchar')}.value = 'closed')
		);
	
	");
}
else 
{
	$installer->run("
		update {$this->getTable('sales_flat_order')} 
		set stocks_updated = 1
		where status in ('complete', 'canceled', 'closed');
	");
}

$installer->run("

-- Insere les enregistrement pour la colonne ordered_qty si ils n existent pas
insert into {$this->getTable('catalog_product_entity_int')} (entity_type_id, attribute_id, store_id, entity_id, value)
	select 
		{$this->getTable('eav_entity_type')}.entity_type_id,
		{$this->getTable('eav_attribute')}.attribute_id,
		0,
		{$this->getTable('catalog_product_entity')}.entity_id,
		0
	from
		{$this->getTable('eav_attribute')},
		{$this->getTable('eav_entity_type')},
		{$this->getTable('catalog_product_entity')}
	where 
		{$this->getTable('eav_entity_type')}.entity_type_code  = 'catalog_product'
		and {$this->getTable('eav_attribute')}.entity_type_id = {$this->getTable('eav_entity_type')}.entity_type_id
		and {$this->getTable('eav_attribute')}.attribute_code = 'ordered_qty'
		and {$this->getTable('catalog_product_entity')}.entity_id not in
		(
			-- liste des produits qui ont une valeur pour ordered_qty
			select 
				{$this->getTable('catalog_product_entity_int')}.entity_id
			from 
				{$this->getTable('catalog_product_entity_int')},
				{$this->getTable('eav_attribute')},
				{$this->getTable('eav_entity_type')}
			where 
				{$this->getTable('eav_entity_type')}.entity_type_code  = 'catalog_product'
				and {$this->getTable('eav_attribute')}.attribute_code = 'ordered_qty'
				and {$this->getTable('eav_attribute')}.entity_type_id = {$this->getTable('eav_entity_type')}.entity_type_id
				and {$this->getTable('catalog_product_entity_int')}.attribute_id = {$this->getTable('eav_attribute')}.attribute_id
				and {$this->getTable('catalog_product_entity_int')}.store_id = 0
		);

-- Idem pour la qte reservee
insert into {$this->getTable('catalog_product_entity_int')}(entity_type_id, attribute_id, store_id, entity_id, value)
	select 
		{$this->getTable('eav_entity_type')}.entity_type_id,
		{$this->getTable('eav_attribute')}.attribute_id,
		0,
		{$this->getTable('catalog_product_entity')}.entity_id,
		0
	from
		{$this->getTable('eav_attribute')},
		{$this->getTable('eav_entity_type')},
		{$this->getTable('catalog_product_entity')}
	where 
		{$this->getTable('eav_entity_type')}.entity_type_code  = 'catalog_product'
		and {$this->getTable('eav_attribute')}.entity_type_id = {$this->getTable('eav_entity_type')}.entity_type_id
		and {$this->getTable('eav_attribute')}.attribute_code = 'reserved_qty'
		and {$this->getTable('catalog_product_entity')}.entity_id not in
		(
			-- liste des produits qui ont une valeur pour ordered_qty
			select 
				{$this->getTable('catalog_product_entity_int')}.entity_id
			from 
				{$this->getTable('catalog_product_entity_int')},
				{$this->getTable('eav_attribute')},
				{$this->getTable('eav_entity_type')}
			where 
				{$this->getTable('eav_entity_type')}.entity_type_code  = 'catalog_product'
				and {$this->getTable('eav_attribute')}.attribute_code = 'reserved_qty'
				and {$this->getTable('eav_attribute')}.entity_type_id = {$this->getTable('eav_entity_type')}.entity_type_id
				and {$this->getTable('catalog_product_entity_int')}.attribute_id = {$this->getTable('eav_attribute')}.attribute_id
				and {$this->getTable('catalog_product_entity_int')}.store_id = 0
		);
		
	
	");

																																											
$installer->endSetup();

