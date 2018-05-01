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
class MDN_Orderpreparation_Helper_PickupDeliveryOrders extends Mage_Core_Helper_Abstract {

    /**
     * return pickup delivery orders
     */
    public function getOrders() {
        $collection = mage::getModel('sales/order')
                        ->getCollection()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('status', array('nin' => array('canceled')))
                        ->addExpressionAttributeToSelect('billing_name',
                                'CONCAT({{customer_firstname}}, " ", {{customer_lastname}}, " ")',
                                array('customer_firstname', 'customer_lastname'));

        $this->addShippingMethodFilter($collection);

        return $collection;
    }

    /**
     * return array with "pickup" shipping methods
     */
    public function getAllowedShippingMethods($addEmpty = false) {
        $methods = array();

        //todo : add this in system > config
        if ($addEmpty)
            $methods[] = array('label' => '', 'value' => '');

        $methods[] = array('label' => 'Magasin Toulon', 'value' => 'RetraitMagasin_');
        $methods[] = array('label' => 'Magasin Paris', 'value' => 'RetraitMagasinParis_');

        return $methods;
    }

    /**
     * Add filter to collection to consider only "pickup" shipping methods
     */
    public function addShippingMethodFilter($collection) {
        $methods = $this->getAllowedShippingMethods();
        $attributeValues = array();
        foreach ($methods as $m) {
            $attributeValues[] = $m['value'];
        }

        $collection->addAttributeToFilter('shipping_method', array('in' => $attributeValues));
        return $collection;
    }

    /**
     * Send email to customer to notify that his order is available for pickup
     * @param <type> $order 
     */
    public function notify($order)
    {
        //#########
        //send email to customer
	$translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $templateId = Mage::getStoreConfig('orderpreparation/pickupdeliveryorders/email_template');
        $identityId = Mage::getStoreConfig('orderpreparation/pickupdeliveryorders/email_identity');
        $mailTo = $order->getcustomer_email();
        
        //define data array
        $data = array
        	(
        		'customer_name' => $order->getShippingAddress()->getName(),
        		'order_increment_id' => $order->getincrement_id(),
        		'shipping_method' => $order->getshipping_description(),
        		'current_date' => date('Y-m-d'),
                        'store_name' => $order->getStore()->getGroup()->getName()
        	);

        //envoi le mail
        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area'=>'adminhtml', 'store'=>0))
            ->sendTransactional(
                $templateId,
                $identityId,
                $mailTo,
                '',
                $data,
                null,
                null);
        $translate->setTranslateInline(true);

        //#########
        //store notification information in order
        $order->setpickup_notification_time(date('Y-m-d H:i'));
        $order->setpickup_is_notified(1);
        $order->save();

        //add organizer
        $Task = Mage::getModel('Organizer/Task')
                ->setot_author_user(10)
                ->setot_created_at(date('Y-m-d H:i'))
                ->setot_caption('Customer notified that order is available for pickup')
                ->setot_description('')
                ->setot_entity_type('order')
                ->setot_entity_id($order->getId())
                ->setot_entity_description('Order #'.$order->getincrement_id())
                ->save();

    }
}