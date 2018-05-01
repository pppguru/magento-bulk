<?php
class Bulksupplements_CustomReports_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	/**
	 * Renders grid column
	 *
	 * @param   Varien_Object $row
	 * @return  string
	 */
	public function render(Varien_Object $row)
	{
		/*$dataArr = array();
		foreach ($this->getColumn()->getIndex() as $index) {
			if ($data = $row->getData($index)) {
				$dataArr[] = $data;
			}
		}
		$data = join($this->getColumn()->getSeparator(), $dataArr);
		// TODO run column type renderer
		return $data;*/

//        return $row->getStampCost() + $row->getInkCost() + $row->getFormCost();
		$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'weight_form');
		$options =  array();
		foreach( $attribute->getSource()->getAllOptions(true, true) as $option ) {
			 $label = str_replace('Pure Powder', '', $option['label']);

			 if (strpos($label, 'kg') !== false) {
				$label = str_replace('kg', '', $label);
			 } else {
				 $label = str_replace('g', '', $label);
				 $label = $label * 0.001;
			 }

			 /*if (strstr($label, 'kg') == 'kg') {
				$label = str_replace('kg', '', $label);
			 } elseif (strstr($label, 'g') == 'g') {
				$label = str_replace('g', '', $label);
				$label = $label * 0.001;
			 } else {
				$label = 0;
			 }*/

			 $options[$option['value']] = $label;
		}

		$data = $options[$row->getWeightForm()] * $row->getOrderedQty();
		return $data;
	}

}
