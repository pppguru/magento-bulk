<?php

class Extendware_EWCore_Block_Adminhtml_System_Information_Edit extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('back');
        $this->_removeButton('saveandreload');
    }

    public function getHeaderText()
    {
        return $this->__('System Information');
    }
}