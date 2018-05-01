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

//add delivery percent field + update values
$installer->run("
	ALTER TABLE {$this->getTable('purchase_order')} ADD `po_delivery_percent` INT NOT NULL DEFAULT 0;
	ALTER TABLE {$this->getTable('purchase_order')} ADD `po_supplier_notification_date` DATETIME NULL;
	
	update 
		{$this->getTable('purchase_order')},
		(
		select pop_order_num, ((sum(pop_supplied_qty)) / sum(pop_qty) * 100) as delivery_percent
		from {$this->getTable('purchase_order_product')} 
		group by pop_order_num
		) tbl_delivery_percent
	set {$this->getTable('purchase_order')}.po_delivery_percent = tbl_delivery_percent.delivery_percent
	where {$this->getTable('purchase_order')}.po_num = tbl_delivery_percent.pop_order_num;
	
	update  {$this->getTable('purchase_order')} set po_delivery_percent = 100 where po_delivery_percent < 0;

	insert into {$this->getTable('core_email_template')}  
	(template_code, template_text, template_type, template_subject)
	values
	(
		'Commande fournisseur',
		'<p>Bonjour<br />vous trouverez ci-joint notre commande.
		<p>{{var message}}</p>
		<br>Cordialement',
		2,
		'Commande pour {{var company_name}}'
	);
	 
	insert into {$this->getTable('core_email_template')}  
	(template_code, template_text, template_type, template_subject)
	values
	(
		'Purchase Order',
		'<p>Hello<br />Enclosed you will find our order.
		<p>{{var message}}</p>
		<br>Best regards',
		2,	'Purchase order from {{var company_name}}'
	);
	
	ALTER TABLE  {$this->getTable('purchase_order')} CHANGE  `po_supply_date`  `po_supply_date` DATE NULL , CHANGE  `po_invoice_date`  `po_invoice_date` DATE NULL;
	update {$this->getTable('purchase_order')} set po_supply_date = null WHERE po_supply_date = '0000-00-00';
	update {$this->getTable('purchase_order')} set po_invoice_date = null WHERE po_invoice_date = '0000-00-00';
	update {$this->getTable('purchase_order')} set po_payment_date = null WHERE po_payment_date = '0000-00-00';
");


$installer->endSetup();
