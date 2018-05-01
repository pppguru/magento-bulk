<?php

/**

 * Magento

 *

 * NOTICE OF LICENSE

 *

 * This source file is subject to the Open Software License (OSL 3.0)

 * that is bundled with this package in the file LICENSE.txt.

 * It is also available through the world-wide-web at this URL:

 * http://opensource.org/licenses/osl-3.0.php

 * If you did not receive a copy of the license and are unable to

 * obtain it through the world-wide-web, please send an email

 * to license@magentocommerce.com so we can send you a copy immediately.

 *

 * DISCLAIMER

 *

 * Do not edit or add to this file if you wish to upgrade Magento to newer

 * versions in the future. If you wish to customize Magento for your

 * needs please refer to http://www.magentocommerce.com for more information.

 *

 * @category    Mage

 * @package     Mage_Adminhtml

 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)

 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

 */



/**

 * Adminhtml grid item renderer concat

 *

 * @category   Mage

 * @package    Mage_Adminhtml

 * @author     Magento Core Team <core@magentocommerce.com>

 */



class Bulksupplements_ReportDetails_Block_Adminhtml_Widget_Grid_Column_Renderer_Kg

	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract

{


	private $cur_colmn = '';
	/**

	 * Renders grid column

	 *

	 * @param   Varien_Object $row

	 * @return  string

	 */
	public function __construct($column)
	{
		 $this->cur_colmn = $column;
	}
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
	
			$row_data = $row->getData();
			if($this->cur_colmn == "amazon_merchant_kg")
			{
				
				if($row_data['amazon_units']!=0)
					$data = $options[$row->getWeightForm()] *  $row_data['amazon_units'];
				else
					$data = "0";
				
			}
			else if($this->cur_colmn == "bulkSupplements_kg")
			{
				
				if($row_data['bulkSupplements']!=0)
					$data = $options[$row->getWeightForm()] *  $row_data['bulkSupplements'];
				else
					$data = "0";
			}
			
			else if($this->cur_colmn == "amazon_fba_kg")
			{
				
				if($row_data['amazon_fba_units']!=0)
					$data = $options[$row->getWeightForm()] *  $row_data['amazon_fba_units'];
				else
					$data = "0";
			}
			else if($this->cur_colmn == "amazon_merchant_percentage")
			{
				
				if($row_data['amazon_units']!=0)
				{
					$data = (round ((100*$row_data['amazon_units'])/$row->getOrderedQty(),2)) . "%";
				}
				else
				{
					$data = "0%";
				}
			}
			else if($this->cur_colmn == "bulkSupplements_percentage")
			{
				
				if($row_data['bulkSupplements']!=0)
				{
					$data = (round ((100*$row_data['bulkSupplements'])/$row->getOrderedQty(),2)) . "%";
				}
				else
				{
					$data = "0%";
				}
			}
			else if($this->cur_colmn == "amazon_fba_percentage")
			{
				
				if($row_data['amazon_fba_units']!=0)
				{
					$data = (round ((100*$row_data['amazon_fba_units'])/$row->getOrderedQty(),2)) . "%";
				}
				else
				{
					$data = "0%";
				}
			}
			else if($this->cur_colmn == "amazon_fba_units")
			{

				
				if($row_data['amazon_fba_units']!= 'NULL' && $row_data['amazon_fba_units'] > 0)
				{
					$data = $row_data['amazon_fba_units'];
				}
				else
				{
					$data = "0";
				}
			}
			else if($this->cur_colmn == "amazon_units")
			{

				
				if($row_data['amazon_units']!= 'NULL' && $row_data['amazon_units'] > 0)
				{
					$data = $row_data['amazon_units'];
				}
				else
				{
					$data = "0";
				}
			}
			
			else
			{
				$data = $options[$row->getWeightForm()] * $row->getOrderedQty();
			}
			return $data;

	}



}

