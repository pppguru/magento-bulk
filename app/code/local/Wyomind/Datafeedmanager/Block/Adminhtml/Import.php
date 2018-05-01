<?php

class Wyomind_Datafeedmanager_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'import';
        $this->_blockGroup = 'datafeedmanager';
        $this->_controller = 'adminhtml_import';

       $this->_updateButton('save', 'label', Mage::helper('datafeedmanager')->__('Import now'));
       $this->_removeButton('delete');
       $this->_removeButton('reset');


    }
    
    

    public function getHeaderText() {

        return Mage::helper('datafeedmanager')->__('');
    }

    

}