<?php

class MDN_SalesOrderPlanning_Block_Adminhtml_Sales_Order_View_Tab_Planning
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	private $_planning = null;
	
    protected function _construct()
    {
        parent::_construct();

        $this->setId('salesorderplanning');

        // Mage_Sales_Model_Order::STATE_CANCELED
        if ($this->getOrder()->getstate() != 'canceled')
	        $this->setTemplate('sales/order/view/tab/Planning.phtml');
    }
	
    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Retrieve source model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }
    
    /**
     * Return planning object
     *
     */
    public function getPlanning()
    {
    	if ($this->_planning == null)
    	{
    		$this->_planning = mage::helper('SalesOrderPlanning/Planning')->getPlanningForOrder($this->getOrder());
    		if ($this->_planning)
    		{
    			//if planning is not up to date, refresh it
				if ($this->_planning->getpsop_need_update() == 1)
				{
					mage::helper('SalesOrderPlanning/Planning')->updatePlanning($this->getOrder()->getId());
					$this->_planning = mage::helper('SalesOrderPlanning/Planning')->getPlanningForOrder($this->getOrder());
				}
    		}
    	}
    	return $this->_planning;
    }
    
    /**
     * return url to forcer planning creation
     *
     * @return unknown
     */
    public function getUrlToForceCreation()
    {
    	return $this->getUrl('adminhtml/SalesOrderPlanning_Planning/Create', array('order_id' => $this->getOrder()->getId()));
    }
    
    
    public function getResetUrl()
    {
    	return $this->getUrl('adminhtml/SalesOrderPlanning_Planning/reset', array('psop_id' => $this->getPlanning()->getId()));
    }
    
    
    public function getSubmitUrl()
    {
    	return $this->getUrl('adminhtml/SalesOrderPlanning_Planning/Save');
    }
    
    public function getUpdateNowUrl()
    {
    	return $this->getUrl('adminhtml/SalesOrderPlanning_Planning/Update', array('psop_id' => $this->getPlanning()->getId()));
    }
    
    /**
     * Override formatDate to return empty if no date set
     *
     */
    public function formatDateWithEmpty($date, $format, $bool)
    {
		if ($date != '')    	
			return $this->formatDate($date, $format, $bool);
		else 
			return '';
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('sales')->__('Planning');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('Planning');
    }

    public function canShowTab()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/sales_order_planning/display_planning');
    }

    public function isHidden()
    {
        return false;
    }
}