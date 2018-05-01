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

class MDN_Purchase_Block_Widget_Column_Renderer_SupplierInvoice_Delete
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
		$html = '';
		$supplierInvoice = Mage::getModel('Purchase/PurchaseSupplierInvoice')->load($row->getpsi_id());
		if($supplierInvoice->getId()>0) {
			$po = mage::getModel('Purchase/Order')->load($supplierInvoice->getpsi_po_id());
			if($po->getId()>0) {
				$label = $this->__('Are you sure you want to delete this invoice of the purchase order %s for the supplier %s ?', $po->getpo_order_id(), $po->getSupplier()->getsup_name());
				$url = $this->getUrl('adminhtml/Purchase_SupplierInvoice/Delete', array('psi_id' => $row->getpsi_id(),'po_num' => $supplierInvoice->getpsi_po_id()));
				$html .= '<button onclick="confirmDelete(\'' . $url . '\', \'' . $label . '\');"; class="scalable delete" type="button"><span></span></button>';
			}
		}
		return $html;
    }

	public function renderExport(Varien_Object $row)
	{
		return '';
	}
    
}