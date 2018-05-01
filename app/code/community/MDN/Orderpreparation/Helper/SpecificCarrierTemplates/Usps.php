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
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Usps extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract {

    const CARRIER_CODE  = 'usps';

    /**
     * Generate XML output
     *
     * @param unknown_type $orderPreparationCollection
     */
    public function createExportFile($orderPreparationCollection) {

        $output = '';

        foreach ($orderPreparationCollection as $orderToPrepare) {

            //check shipping method
            $order = mage::getModel('sales/order')->load($orderToPrepare->getorder_id());
            if (!$this->allowableShippingMethod($order))
                continue;

            $shipmentId = $orderToPrepare->getshipment_id();

            //because on each call it will recreate a new label a and a new tracking and import it back
            if ($this->isShipmentAlreadyContainsUspsTracking($shipmentId))
                continue;

            $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
            $debug = false;
            $data = $this->getTrackingAndLabel($shipment,$order->getshipping_method(),$debug);
            $trackingNumber = $data['tracking_number'];//TRACKING
            $shippingLabelImage = $data['label_content'];//PNG

            //include a security to don't add the same tracking many time
            $shippingLabelDisplayed = self::CARRIER_CODE;
            mage::helper('Orderpreparation/Tracking')->addTrackingToShipment($trackingNumber, $shipmentId, self::CARRIER_CODE, $shippingLabelDisplayed);

            return $this->_processDownload($shippingLabelImage,"image/png",$shipmentId.'_label');

        }
        return $output;

    }

    public function isShipmentAlreadyContainsUspsTracking($shipmentId){
        $exist = false;

            $collection = Mage::getResourceModel('sales/order_shipment_track_collection')->setShipmentFilter($shipmentId);

            foreach ($collection->getTracksCollection() as $track) {
                if ($track->getcarrier_code() == self::CARRIER_CODE) {
                    $exist = true;
                    break;
                }
            }

        return $exist;
    }


    /**
     * Check that shipping method is USPS or wsaendicia
     * @param <type> $order
     */
    protected function allowableShippingMethod($order)
    {
        $shippingMethod = $order->getshipping_method();
        $t = explode('_', $shippingMethod);
        if (isset($t[0]) && ($t[0] == 'usps' ||  $t[0] =='wsaendicia'))
            return true;
        return false;
    }

    protected function _processDownload($resource, $contentType,$fileName) {
        header('Content-type: '.$contentType);
        echo $resource;
        die();
    }


    /**
     * Method to import trackings
     * @param <type> $t_lines
     */
    public function importTrackingFile($t_lines) {
        throw new Exception('Not implemented');
    }



    public $packageWeight = null;
    public $packageValue = null;
    public $packageItems = null;
    public $packageParams = null;

    /**
     * @param $shipment
     * return $request :
     * Array
     * (
     * [0] => order_shipment
     * [1] => shipper_contact_person_name
     * [2] => shipper_contact_person_first_name
     * [3] => shipper_contact_person_last_name
     * [4] => shipper_contact_company_name
     * [5] => shipper_contact_phone_number
     * [6] => shipper_email
     * [7] => shipper_address_street
     * [8] => shipper_address_street1
     * [9] => shipper_address_street2
     * [10] => shipper_address_city
     * [11] => shipper_address_state_or_province_code
     * [12] => shipper_address_postal_code
     * [13] => shipper_address_country_code
     * [14] => recipient_contact_person_name
     * [15] => recipient_contact_person_first_name
     * [16] => recipient_contact_person_last_name
     * [17] => recipient_contact_company_name
     * [18] => recipient_contact_phone_number
     * [19] => recipient_email
     * [20] => recipient_address_street
     * [21] => recipient_address_street1
     * [22] => recipient_address_street2
     * [23] => recipient_address_city
     * [24] => recipient_address_state_or_province_code
     * [25] => recipient_address_region_code
     * [26] => recipient_address_postal_code
     * [27] => recipient_address_country_code
     * [28] => shipping_method
     * [29] => package_weight
     * [30] => packages
     * [31] => base_currency_code
     * [32] => store_id
     * [33] => package_id
     * [34] => packaging_type
     * [35] => package_value
     * [36] => services
     * [37] => package_params
     * [38] => package_items
     * [39] => insurance_required
     * [40] => insurance_value
     * [41] => selected_ship_method
     * )
     */
    public function getTrackingAndLabel($shipment, $shippingMethod = 'Priority', $debug = false)
    {
        try {
            //get required variables to build $request
            $order = $shipment->getOrder();
            $user = Mage::getSingleton('admin/session')->getUser();
            $shippingAddress = Mage::getModel('sales/order_address')->load($order->getshipping_address_id());
            $shippingRegion = Mage::getModel('directory/region')->load($shippingAddress->getregion_id());
            $shipperRegion = Mage::getModel('directory/region')->load(mage::getStoreConfig('shipping/origin/region_id', $order->getStoreId()));
            $shippingMethod = trim(str_replace('wsaendicia_','',$shippingMethod));


            //build $request
            $data['order_shipment'] = $shipment;
            $data['shipper_contact_person_name'] = $user->getName();
            $data['shipper_contact_person_first_name'] = $user->getfirstname();
            $data['shipper_contact_person_last_name'] = $user->getlastname();
            $data['shipper_contact_company_name'] = mage::getStoreConfig('general/store_information/name', $order->getStoreId());
            $data['shipper_contact_phone_number'] = trim(str_replace('-','',mage::getStoreConfig('general/store_information/phone', $order->getStoreId())));
            $data['shipper_email'] = mage::getStoreConfig('contacts/email/recipient_email', $order->getStoreId());
            $data['shipper_address_street'] = mage::getStoreConfig('shipping/origin/street_line1', $order->getStoreId());
            $data['shipper_address_street1'] = mage::getStoreConfig('shipping/origin/street_line1', $order->getStoreId());
            $data['shipper_address_street2'] = mage::getStoreConfig('shipping/origin/street_line2', $order->getStoreId());
            $data['shipper_address_city'] = mage::getStoreConfig('shipping/origin/city', $order->getStoreId());
            $data['shipper_address_state_or_province_code'] = $shipperRegion->getCode();
            $data['shipper_address_postal_code'] = mage::getStoreConfig('shipping/origin/postcode', $order->getStoreId());
            $data['shipper_address_country_code'] = mage::getStoreConfig('shipping/origin/country_id', $order->getStoreId());
            $data['recipient_contact_person_name'] = $shippingAddress->getName();
            $data['recipient_contact_person_first_name'] = $shippingAddress->getfirstname();
            $data['recipient_contact_person_last_name'] = $shippingAddress->getlastname();
            $data['recipient_contact_company_name'] = $shippingAddress->getcompany();
            $data['recipient_contact_phone_number'] = $shippingAddress->gettelephone();
            $data['recipient_email'] = $shippingAddress->getemail();
            $data['recipient_address_street'] = implode(', ', $shippingAddress->getstreet());
            $data['recipient_address_street1'] = $shippingAddress->getStreet(1);
            $data['recipient_address_street2'] = $shippingAddress->getStreet(2);
            $data['recipient_address_city'] = $shippingAddress->getcity();
            $data['recipient_address_state_or_province_code'] = $shippingRegion->getCode();
            $data['recipient_address_region_code'] = $shippingRegion->getCode();
            $data['recipient_address_postal_code'] = $shippingAddress->getpostcode();
            $data['recipient_address_country_code'] = $shippingAddress->getcountry_id();
            $data['shipping_method'] = $shippingMethod;
            $data['package_weight'] = 1;
            $data['packages'] = $this->getPackage($shipment);
            $data['base_currency_code'] = $order->getbase_currency_code();
            $data['store_id'] = $order->getStoreId();
            $data['package_id'] = 1;
            $data['packaging_type'] = 'Parcel';
            $data['package_value'] = '';
            $data['services'] = False;
            $data['package_items'] = new Varien_Object($this->getPackageItems($shipment));
            $data['package_params'] = new Varien_Object($this->getPackageParams());
            $data['insurance_required'] = '';
            $data['insurance_value'] = '';
            $data['selected_ship_method'] = '';



            /*if($debug)
                die('<pre>'.print_r($data, true));*/

            $request = mage::getModel('Shipping/Shipment_Request');
            $request->setData($data);

            if($debug){
                echo "<pre>";
                foreach($request->getData() as $k => $v)
                {
                    if($k == 'order_shipment')
                        continue;

                    echo "\n $k => ".print_r($v, true);
                }
                die();
            }

            //build Endicia stream
            $response = Mage::getModel('wsaendicia/carrier_endicia')->requestToShipment($request);

            if(!is_object($response))
                throw new Exception('tracking or label missing');

            $errors = $response->getData('errors');
            if(!empty($errors))
                throw new Exception ($errors);

            $info = $response->getData('info');
            $info = $info[0];

            if(!is_array($info) || !isset($info['tracking_number'])  || !isset($info['label_content']))
                throw new Exception('tracking or label missing');

            return $info;
        } catch (Exception $e) {
            throw new Exception ($e->getMessage());
        }

    }


    function getPackage($shipment)
    {
        $this->packageWeight = 0;
        $this->packageValue = 0;

        $package = array();
        $package[1] = array(
            'items' => $this->getPackageItems($shipment),
            'params' => $this->getPackageParams()
        );

        return $package;
    }

    public function getPackageItems($shipment)
    {
        if ($this->packageItems !== null)
            return $this->packageItems;

        $this->packageItems = array();
        foreach ($shipment->getItemsCollection() as $item) {
            $orderItemId = $item->getorder_item_id();
            if($orderItemId) {
                $orderItem = mage::getModel('sales/order_item')->load($orderItemId);
                if ($orderItem->getproduct_type() == 'simple'){
                    $this->packageWeight += ($item->getqty() * $item->getweight());
                }
                $this->packageValue += ($item->getqty() * $item->getprice());
                $this->packageItems[$item->getorder_item_id()] = $this->getPackageItem($item);
            }
        }

        if($this->packageWeight == 0){
            $this->packageWeight = 1;
        }

        //convert pound to ounce
        $this->packageWeight = round(($this->packageWeight/16),4);

        return $this->packageItems;
    }


    public function getPackageItem($item)
    {
        return array(
            'qty' => $item->getqty(),
            'customs_value' => $item->getprice(),
            'price' => $item->getprice(),
            'name' => $item->getname(),
            'weight' => $item->getweight(),
            'product_id' => $item->getproduct_id(),
            'order_item_id' => $item->getorder_item_id()
        );
    }

    public function getPackageParams()
    {
        if ($this->packageParams !== null)
            return $this->packageParams;

        $weights = Mage::registry('weights');

        if ($weights != null && isset($weights[0]) && !empty($weights[0]) && is_numeric($weights[0]) )
            $weight = $weights[0];
        else
            $weight = $this->packageWeight;

        $this->packageParams = array(
            'container' => 'Parcel',
            'weight' => $weight,
            'customs_value' => $this->packageValue,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'weight_units' => 'POUND',
            'dimension_units' => 'INCH',
            'content_type' => '',
            'content_type_other' => '',
            'delivery_confirmation' => False
        );

        return $this->packageParams;
    }

}