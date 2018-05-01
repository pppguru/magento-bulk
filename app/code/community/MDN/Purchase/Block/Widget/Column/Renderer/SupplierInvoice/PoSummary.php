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

class MDN_Purchase_Block_Widget_Column_Renderer_SupplierInvoice_PoSummary
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
		$html = '';
		$collection = Mage::getModel('Purchase/PurchaseSupplierInvoice')
				->getCollection()
				->addFieldToFilter('psi_po_id',$row->getpo_num())
				->setOrder('psi_status','asc');

		if($collection->getSize() > 0) {
			$html .= '<table>';
			foreach ($collection as $invoice) {
				$html .= '<tr>';
					$html .= '<td>'.mage::helper('purchase/SupplierInvoice')->getStatusHtmlColor($invoice->getpsi_status(),$invoice->getpsi_invoice_id()).'</td>';
					$html .= '<td>'.$invoice->getpsi_amount().'</td>';
				$html .= '</tr>';
			}
			$html .= '</table>';
		}
		return $html;
    }

	public function renderExport(Varien_Object $row)
	{
		return '';
	}
    
}