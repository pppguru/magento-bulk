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

class MDN_SalesOrderPlanning_Block_Widget_Grid_Column_Renderer_OrderPlanning extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
		$html = $this->__('No planning');

		$planning = $this->getPlanning($row);

    	if ($planning)
    	{
    		$html = $this->getPlanningHtml($planning);
    	}

		return $html;
    }

	public function getPlanningHtml($planning){
		$html = '<div class="nowrap" style="text-align: left;">';
		if ($planning->getFullstockDate() != '')
			$html .= mage::helper('SalesOrderPlanning')->__('Prepare').' : <font color="'.$this->getColorForDate($planning->getFullstockDate()).'">'.mage::helper('core')->formatDate($planning->getFullstockDate(), 'short').'</font>';
		if ($planning->getShippingDate() != '')
			$html .= '<br>'.mage::helper('SalesOrderPlanning')->__('Ship').' : <font color="'.$this->getColorForDate($planning->getShippingDate()).'">'.mage::helper('core')->formatDate($planning->getShippingDate(), 'short').'</font>';
		if ($planning->getDeliveryDate() != '')
			$html .= '<br>'.mage::helper('SalesOrderPlanning')->__('Delivery').' <font color="'.$this->getColorForDate($planning->getDeliveryDate()).'">: '.mage::helper('core')->formatDate($planning->getDeliveryDate(), 'short').'</font>';
		$html .= '</div>';

		return $html;
	}

	public function getPlanning($row){
		//get planning for order prepration renderer
		$orderId = $row->getopp_order_id();

		//get planning for sales order grid renderer
		if(!$orderId){
			$orderId = $row->getId();
		}

		return mage::helper('SalesOrderPlanning/Planning')->getPlanningForOrderId($orderId);
	}

    /**
     * Enter description here...
     *
     * @param unknown_type $date
     */
    public function getColorForDate($date)
    {
    	$timestamp = strtotime($date);
    	$now = time();
    	
    	if ($timestamp < $now)
    		$colorCode = '#ff0000';
    	else 
    	{
    		$diff = $timestamp - $now;
    		if ($diff > 3600 * 24 * 2)
    			$colorCode = '#00FF00';
    		else 
    			$colorCode = 'orange';
    	}
    	
    	return $colorCode;
    }

	public function renderExport(Varien_Object $row)
	{
		$planning = $this->getPlanning($row);
		$txt = '';
		if ($planning){
			if ($planning->getFullstockDate() != '')
				$txt .= mage::helper('SalesOrderPlanning')->__('Prepare').' : '.mage::helper('core')->formatDate($planning->getFullstockDate(), 'short');
			if ($planning->getShippingDate() != '')
				$txt .= ' '.mage::helper('SalesOrderPlanning')->__('Ship').' : '.mage::helper('core')->formatDate($planning->getShippingDate(), 'short');
			if ($planning->getDeliveryDate() != '')
				$txt .= ' '.mage::helper('SalesOrderPlanning')->__('Delivery').' : '.mage::helper('core')->formatDate($planning->getDeliveryDate(), 'short');
	    	}	
		return $txt;
	}
    
}
