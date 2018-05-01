<?php

class MDN_Shipworks_Model_System_Config_Source_Shipping_AllMethods
{
    public function toOptionArray($isActiveOnlyFlag=false)
    {
        $methods = array(array('value'=>'', 'label'=>''));
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        foreach ($carriers as $carrierCode=>$carrierModel) {
            if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            $carrierMethods = $carrierModel->getAllowedMethods();
            if (!$carrierMethods) {
                continue;
            }
            $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
            $methods[$carrierCode] = array(
                'label'   => $carrierTitle,
                'value' => array(),
            );
            foreach ($carrierMethods as $methodCode=>$methodTitle) {
                $methods[$carrierCode]['value'][] = array(
                    'value' => $carrierCode.'_'.$methodCode,
                    'label' => '['.$carrierCode.'] '.$methodTitle,
                );
            }
        }

        //append methods from DB
        $methods['db'] = array(
            'label'   => 'Methods from DB',
            'value' => array(),
        );

        $sql = 'select distinct '.Mage::getConfig()->getTablePrefix().'shipping_method from sales_flat_order order by shipping_method';
        $collection = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchCol($sql);
        foreach($collection as $item)
        {
            $methods['db']['value'][] = array(
                                    'value' => $item,
                                    'label' => $item,
                                );
        }

        return $methods;
    }
}