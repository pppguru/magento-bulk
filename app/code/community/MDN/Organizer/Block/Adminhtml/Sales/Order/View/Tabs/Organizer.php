<?php

class MDN_Organizer_Block_Adminhtml_Sales_Order_View_Tabs_Organizer extends Mage_Adminhtml_Block_Sales_Order_Abstract implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected function _construct() {
        parent::_construct();

        $this->setTemplate('Organizer/Sales/Order/View/Tab/Organizer.phtml');
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder() {
        return Mage::registry('current_order');
    }

    /**
     * Retrieve source model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource() {
        return $this->getOrder();
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel() {
        return Mage::helper('Organizer')->__('Organizer');
    }

    public function getTabTitle() {
        return Mage::helper('Organizer')->__('Organizer');
    }

    public function canShowTab() {
        return ($this->getOrder()->getId());
    }

    public function isHidden() {
        return false;
    }

}