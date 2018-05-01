<?php

class Wyomind_Datafeedmanager_Block_Adminhtml_Import_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('datafeedmanager_import');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('datafeedmanager')->__('Data Feed Manager'));
        
    }

    protected function _beforeToHtml() {
        $this->addTab('uploader', array(
            'label' => Mage::helper('datafeedmanager')->__('Template Import'),
            'title' => Mage::helper('datafeedmanager')->__('Template Import'),
            'content' => $this->getLayout()->createBlock('datafeedmanager/adminhtml_import_edit_tab_uploader')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}