<?php

class MDN_AdvancedStock_Block_Inventory_Edit_Tabs_ApplyInventory extends Mage_Adminhtml_Block_Widget_Form
{
    
    public function initForm()
    {
        $form = new Varien_Data_Form();

        $inventory = Mage::registry('current_inventory');

        $fieldset = $form->addFieldset('apply_fieldset',
            array('legend'=>Mage::helper('AdvancedStock')->__('Apply stock take'))
        );
        
        $fieldset->addField('apply_comment', 'label', array(
            'name'=>'apply_comment',
            'label' => Mage::helper('AdvancedStock')->__('Notice')
        ));
        
        $fieldset->addField('apply_stock_movement_label', 'text', array(
            'name'=>'apply_stock_movement_label',
            'label' => Mage::helper('AdvancedStock')->__('Stock movement label')
        ));

        $fieldset->addField('apply_simulation', 'select', array(
            'name'=>'apply_simulation',
            'label' => Mage::helper('AdvancedStock')->__('Simulation mode'),
            'options' => array(0 => $this->__('No'), 1 => $this->__('Yes'))
        ));

        if($inventory->getei_stock_take_mode() == MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_MODE_BY_LOCATION){

            $fieldset->addField('apply_only_for_scanned_location', 'select', array(
                'name'=>'apply_only_for_scanned_location',
                'label' => Mage::helper('AdvancedStock')->__('Apply only for scanned location'),
                'options' => array(0 => $this->__('No'), 1 => $this->__('Yes')),
                'after_element_html' => '<small><br/>'. $this->__('If you Select No, it will set to 0 all products not scanned in your inventory').'</small>',
            ));
            
        }
        
        $fieldset->addField('apply_inventory', 'hidden', array(
            'name'=>'apply_inventory'
        ));
        
        $fieldset->addField('apply_button', 'button', array(
            'name'=>'apply_button',
            'label' => Mage::helper('AdvancedStock')->__('Apply now'),
            'onclick' => "if (confirm('".$this->__('Are you sure ?')."')) { applyInventory(); }"
        ));
        
        $datas = array();
        $datas['apply_button'] = $this->__('Apply now !');
        $datas['apply_simulation'] = 1;
        $datas['apply_only_for_scanned_location'] = 1;
        $datas['apply_stock_movement_label'] = $this->__('Adjustment for %s', $inventory->getei_name());
        $datas['apply_comment'] = $this->__('Applying the stock take will fix product stock levels for the current warehouse based on the data in differences tab. This operation can not be rollbacked');

        $form->setValues($datas);
        $this->setForm($form);
        
        return $this;
    }

    /**
     * 
     */
    protected function getWarehouses()
    {
       $localWarehouses = array();
       
       $collection = Mage::getModel('AdvancedStock/Warehouse')->getCollection();
       foreach($collection as $item)
       {
           $localWarehouses[] = array('value' => $item->getId() ,'label' => $item->getstock_name());
       }
       
       return $localWarehouses;
    }
    
}
