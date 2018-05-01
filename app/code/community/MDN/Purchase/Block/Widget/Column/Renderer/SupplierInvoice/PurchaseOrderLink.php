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

class MDN_Purchase_Block_Widget_Column_Renderer_SupplierInvoice_PurchaseOrderLink
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
		$html = '';
		$supplierInvoice = Mage::getModel('Purchase/PurchaseSupplierInvoice')->load($row->getpsi_id());
		if($supplierInvoice->getId()>0) {
			$po = mage::getModel('Purchase/Order')->load($supplierInvoice->getpsi_po_id());
			if($po->getId()>0) {
				$url = $this->getUrl('adminhtml/Purchase_Orders/Edit', array('po_num' => $supplierInvoice->getpsi_po_id()));
				$label = $po->getpo_order_id();
				$html = '<a href="'.$url.'" target="_blanck">'.$label.'</a>';
			}
		}
		return $html;
    }

	public function renderExport(Varien_Object $row)
	{
		$html = '';
		$supplierInvoice = Mage::getModel('Purchase/PurchaseSupplierInvoice')->load($row->getpsi_id());
		if($supplierInvoice->getId()>0) {
			$po = mage::getModel('Purchase/Order')->load($supplierInvoice->getpsi_po_id());
			if($po->getId()>0) {
				$html = $po->getpo_order_id();
			}
		}
		return $html;
	}
    
}