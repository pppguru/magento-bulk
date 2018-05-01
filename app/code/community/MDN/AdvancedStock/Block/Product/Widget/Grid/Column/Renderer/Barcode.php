<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_Barcode
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
		return $this->getFormattedBarcodeList($row->getId(),'<br/>');
    }

	public function renderExport(Varien_Object $row)
	{
		return $this->getFormattedBarcodeList($row->getId(),', ');

	}
	
	private function getFormattedBarcodeList($productId,$separator){
		$barcodeListForProduct = array();
		$barcodeCollection = mage::helper('AdvancedStock/Product_Barcode')->getBarcodesForProduct($productId);
		foreach ($barcodeCollection as $barcode) {
			$barcodeListForProduct[] = $barcode['ppb_barcode'];
		}
		return implode($separator, $barcodeListForProduct);
	}
    
}

