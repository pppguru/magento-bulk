<?php

class Wyomind_Datafeedmanager_Block_Adminhtml_Import_Edit_Tab_Uploader extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('uploader', array('legend' => Mage::helper('datafeedmanager')->__('Choose a template to import')));


        $fieldset->addField('file', 'file', array(
            'label' => Mage::helper('datafeedmanager')->__('DFM template file'),
            'name' => 'file',
            'required'=>true
        ));


        return parent::_prepareForm();
    }

}