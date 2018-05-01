<?php

class MDN_ProductReturn_Block_Front_Guest_Form extends Mage_Core_Block_Template
{

    public function getFormData()
    {
        $data = new Varien_Object();
        
        //init customer object using order
        $order = $this->getOrder();       
        $customerData = new Varien_Object();
        
        if($order == NULL)
            return 0;
        
        foreach($order->getData() as $k => $v) {
            if (preg_match('/^customer_/', $k))
                    $customerData[str_replace ('customer_', '', $k)] = $v;
        }
        
        $data->addData($customerData->getData());
        $data->setCustomerData(1);        
        return $data;
    }
    
    public function getOrder()
    {
        Mage::unregister('current_order');
        Mage::helper('sales/guest')->loadValidOrder();
        return Mage::registry('current_order');
    }
    
    /**
     * 
     * @return type
     */
    public function isNewsletterEnabled()
    {
        return Mage::helper('core')->isModuleOutputEnabled('Mage_Newsletter');
    }
    
}