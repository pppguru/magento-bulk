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
class MDN_Orderpreparation_Helper_CarrierTemplate extends Mage_Core_Helper_Abstract {

    /**
     * Return template object for carrier
     *
     * @param unknown_type $carrier
     */
    public function getTemplateForCarrier($shippingMethod) {
        $obj = mage::getModel('Orderpreparation/CarrierTemplate')->load($shippingMethod, 'ct_shipping_method');
        if ($obj->getId())
        {
            $this->log('Carrier template for shipping method '.$shippingMethod.' is #'.$obj->getId());
            return $obj;
        }
        else
        {
            $this->log('Carrier template for shipping method '.$shippingMethod.' cant be found');
            return null;
        }
    }

    /**
     * Return matching carrier template for 1 order
     *
     * @param unknown_type $order
     */
    public function getTemplateForOrder($order) {
        $shippingMethod = $order->getshipping_method();
        $t = explode('_', $shippingMethod);
        return $this->getTemplateForCarrier($t[0]);
    }

    public function getTypes() {
        $carrierTemplateTypes = array();

        //----------------------------------------------------------------------
        //CASE 1 : Carrier template made in ERP -> order preparation -> carrier templates
        $carrierTemplateTypes['manual'] = $this->__('manual');

        //----------------------------------------------------------------------
        //CASE 2 : Hardcoded Carrier template or connector to a Tiers extension !

        //Basic Shipping label with only customer adress
        $carrierTemplateTypes['CustomLabel'] = $this->__('Custom Label');

        //UPS
        $carrierTemplateTypes['UpsWorldship'] = $this->__('UPS Worldship');
        $carrierTemplateTypes['UpsBms'] = $this->__('Ups with Boostmyshop');

        //Fedex
        $carrierTemplateTypes['FedexShipManager'] = $this->__('Fedex Ship Manager');

        //Exapack
        $carrierTemplateTypes['Exaprint'] = $this->__('Exaprint');

        //US :  USPS with Endicia
        $carrierTemplateTypes['Usps'] = $this->__('Usps');

        //FR : La Poste - So Collissimo - BMS Extension Connector
        $carrierTemplateTypes['ColissimoBms'] = $this->__('Colissimo with Boostmyshop');

        //FR : Mondial Relay
        $carrierTemplateTypes['MondialRelay'] = $this->__('Mondial Relay');

        //NL : PostNL connector to official extension
        $carrierTemplateTypes['PostNL'] = $this->__('PostNL');

        //UK : Royal mail - BMS Extension Connector
        $carrierTemplateTypes['RoyalMailForErp'] = $this->__('Royal mail with Boostmyshop');

        return $carrierTemplateTypes;
    }
    
    /**
     * 
     * @param type $msg
     */
    protected function log($msg)
    {
        Mage::log($msg, null, 'erp_carrier_template.log');
    }

}