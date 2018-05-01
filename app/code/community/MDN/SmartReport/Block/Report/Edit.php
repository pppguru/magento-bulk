<?php

class MDN_SmartReport_Block_Report_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'Report';
        $this->_blockGroup = 'SmartReport';

        $this->_updateButton('save', 'label', Mage::helper('SmartReport')->__('Save'));
        $this->_updateButton('save', 'id', 'save_button');
        $this->_updateButton('delete', 'label', Mage::helper('SmartReport')->__('Delete'));


    }

    public function getHeaderText()
    {
        if( $this->getReport()->getId() ) {
            return Mage::helper('SmartReport')->__("Edit report '%s' (%s)", $this->escapeHtml($this->getReport()->getName()), $this->getType());
        } else {
            return Mage::helper('SmartReport')->__('New report');
        }
    }

    public function getReport()
    {
        return Mage::registry('smart_report_current_report');
    }

}
