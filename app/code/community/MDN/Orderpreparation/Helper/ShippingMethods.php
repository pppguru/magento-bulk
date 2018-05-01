<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Orderpreparation_Helper_ShippingMethods extends Mage_Core_Helper_Abstract {

    /**
     * Return all shipping methods
     * @return string
     */
    public function getArray() {
        $methods = array();

        $carriers = Mage::getStoreConfig('carriers', 0);
        foreach ($carriers as $key => $item) {
            if (Mage::getStoreConfigFlag('carriers/' . $key . '/active', 0)) {
                //fix method declaration issue
                if (!isset($item['model']) || $item['model'] == '')
                    continue;
                $instance = mage::getModel($item['model']);
                $code = $item['model'];

                $model = mage::getModel($item['model']);
                $allowedMethods = $model->getAllowedMethods();
                if ($allowedMethods) {
                    foreach ($allowedMethods as $methodKey => $method) {
                        $finalKey = $key . '_' . $methodKey;
                        $label = $instance->getConfigData('title') . ' - ' . $method;
                        $methods[$finalKey] = $label;
                    }
                }
            }
        }

        return $methods;
    }

    /**
     * Return shipping method label from code
     * @param <type> $shippingMethod
     * @return string
     */
    public function getLabel($shippingMethod) {
        $label = '';

        foreach($this->getArray() as $key => $label)
        {
            if ($key == $shippingMethod)
                return $label;
        }

        return $label;
    }

    public function updateShippingMethod($orderPreparationIds, $shippingMethod) {
        $shippingDescription = $this->getLabel($shippingMethod);

        //update orders & ordertopreparepending
        $collection = mage::getModel('Orderpreparation/ordertopreparepending')
                        ->getCollection()
                        ->addFieldToFilter('opp_num', array('in' => $orderPreparationIds));
        foreach ($collection as $item) {
            $item->setopp_shipping_method($shippingDescription)->save();

            $orderId = $item->getopp_order_id();
            $this->changeForOrder($orderId, $shippingMethod);
        }
    }

    public function changeForOrder($orderId, $shippingMethod)
    {
        $shippingDescription = $this->getLabel($shippingMethod);
        $order = mage::getModel('sales/order')->load($orderId);
        $order->setshipping_description($shippingDescription)
                ->setshipping_method($shippingMethod)
                ->save();
    }
    
}