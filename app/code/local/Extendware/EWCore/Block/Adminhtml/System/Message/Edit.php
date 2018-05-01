<?php

class Extendware_EWCore_Block_Adminhtml_System_Message_Edit extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_removeButton('reset');
        $this->_removeButton('save');
    }

    public function getHeaderText()
    {
        return $this->__('System Message');
    }
}