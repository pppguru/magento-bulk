<?php

class Extendware_EWCore_Block_Adminhtml_Message_Edit extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_removeButton('reset');
        $this->_removeButton('save');
        
        if ($this->getMessage()->getState() == 'unread') {
	        $this->_addButton('markRead', array(
	            'label'     => $this->__('Mark as Read'),
	        	'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/markRead', array('id' => $this->getMessage()->getId())) . '\')',
	            'class'     => '',
	        ));
        }
        
        if ($this->getMessage()->getUrl()) {
	        $this->_addButton('viewUrl', array(
	            'label'     => $this->__('Visit Url'),
	        	'onclick'   => 'window.open(\'' . $this->getUrl('*/*/goto', array('id' => $this->getMessage()->getId())) . '\', \'message_site\')',
	            'class'     => 'add'
	        ));
        }
    }

    public function getHeaderText()
    {
        return $this->__('Message');
    }
    
	public function getMessage() {
        return Mage::registry('ew:current_message');
    }
}