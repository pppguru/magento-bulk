<?php
/**
* Block qui contiendra les page affichées par les onglets dans la préparation de commandes 
*
**/

class MDN_Orderpreparation_Block_OrderPreparationContainer extends Mage_Adminhtml_Block_Widget_Container
{

    public function __construct()
    {

        //$this->_controller = 'promo_quote';

        parent::__construct();
        
        /*
        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('back');
        $this->_removeButton('reset');
        */
        
        //$this->_updateButton('save', 'label', Mage::helper('salesrule')->__('Save Rule'));
        //$this->_updateButton('delete', 'label', Mage::helper('salesrule')->__('Delete Rule'));

    }

}
