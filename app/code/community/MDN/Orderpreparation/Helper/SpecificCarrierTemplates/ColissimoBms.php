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
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_ColissimoBms extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract
{

    /**
     * Generate XML output
     *
     * @param unknown_type $orderPreparationCollection
     */
    public function createExportFile($orderPreparationCollection)
    {

        $trackingArray = array();

        foreach ($orderPreparationCollection as $orderToPrepare) {

            //check shipping method
            $orderId = $orderToPrepare->getorder_id();
            $order = mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                $shipmentId = $orderToPrepare->getshipment_id();

                if ($order != null && $shipmentId > 0) {
                    $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);

                    if ($shipment->getId()) {
                        if (!$this->checkShippingMethod($order))
                            continue;

                        foreach ($shipment->getAllTracks() as $track) {
                            $trackingNumber = $track->getNumber();
                            if ($trackingNumber != null && strlen($trackingNumber) > 0) {
                                $trackingArray[] = $trackingNumber;
                            }
                        }
                    }
                }
            }
        }

        return Mage::getModel('colissimo/Pdf_Label')->getForSeveralTrackings($trackingArray);
    }

    /**
     * Method to import trackings
     * @param <type> $t_lines
     */
    public function importTrackingFile($t_lines)
    {
        throw new Exception('Not implemented');
    }

    /**
     * Check that shipping method is UPS
     * @param <type> $order
     */
    protected function checkShippingMethod($order)
    {
        $shippingMethod = strtolower($order->getShippingDescription());
        return preg_match('/colissimo/', $shippingMethod);
    }

    /**
     * To implement the form that could be displayed in the packing screen of ERP.
     *
     * @return null
     */
    public function getForm($order)
    {
        $nbOfColumns = 2;
        return $this->getStandardFormField($order, $nbOfColumns);
    }

    public function getFields($order)
    {
        $customFieldsArray = array();

        $entry = array();
        $entry['f_code'] = 'weight';
        $entry['f_name'] = $this->__('Weight');
        $entry['f_value'] = $this->getDefaultWeight($order);
        $entry['f_type'] = 'input';
        $entry['f_display_after'] = $this->getDefaultWeightUnit();
        $customFieldsArray[] = $entry;


        $entry = array();
        $entry['f_code'] = 'height';
        $entry['f_name'] = $this->__('Height');
        $entry['f_value'] = $this->getDefaultHeight($order);
        $entry['f_type'] = 'input';
        $entry['f_display_after'] = $this->getDefaultDimensionUnit();
        $entry['f_on_change'] = 'updateHorsGabarit();';
        $customFieldsArray[] = $entry;

        $entry = array();
        $entry['f_code'] = 'length';
        $entry['f_name'] = $this->__('Length');
        $entry['f_value'] = $this->getDefaultLength($order);
        $entry['f_type'] = 'input';
        $entry['f_display_after'] = $this->getDefaultDimensionUnit();
        $entry['f_on_change'] = 'updateHorsGabarit();';
        $customFieldsArray[] = $entry;

        $entry = array();
        $entry['f_code'] = 'width';
        $entry['f_name'] = $this->__('Width');
        $entry['f_value'] = $this->getDefaultWidth($order);
        $entry['f_type'] = 'input';
        $entry['f_display_after'] = $this->getDefaultDimensionUnit();
        $entry['f_on_change'] = 'updateHorsGabarit();';
        $customFieldsArray[] = $entry;

        $entry = array();
        $entry['f_code'] = 'diam';
        $entry['f_name'] = $this->__('Diameter');
        $entry['f_value'] = $this->getDefaultDiam($order);
        $entry['f_type'] = 'input';
        $entry['f_display_after'] = $this->getDefaultDimensionUnit();
        $entry['f_on_change'] = 'updateHorsGabarit();';
        $customFieldsArray[] = $entry;

        $entry = array();
        $entry['f_code'] = 'parceltype';
        $entry['f_name'] = $this->__('Parcel Type');
        $entry['f_value'] = $this->getDefaultParcelType($order);
        $entry['f_type'] = 'list';
        $entry['f_list_elements'] = '1:Classic;2:Roll';//MDN_Colissimo_Model_System_Config_ParcelType
        $entry['f_on_change'] = 'updateHorsGabarit();';
        $customFieldsArray[] = $entry;

        $entry = array();
        $entry['f_code'] = 'shipmenttype';
        $entry['f_name'] = $this->__('Shipment Type');
        $entry['f_value'] = $this->getDefaultCategorie($order);
        $entry['f_type'] = 'list';
        $entry['f_list_elements'] = '1:Gift;2:Sample;3:Commercial shipment;4:Document;5:Other;6:Return of goods';//MDN_Colissimo_Model_System_Config_Categorie
        $customFieldsArray[] = $entry;

        $entry = array();
        $entry['f_code'] = 'deliverymode';
        $entry['f_name'] = $this->__('Delivery Mode');
        $entry['f_value'] = $this->getOrderDeliveryMode($order);
        $entry['f_type'] = 'list';
        $entry['f_list_elements'] = 'DOM:DOM;RDV:RDV;BPR:BPR;ACP:ACP;CDI:CDI;A2P:A2P;MRL:MRL;CIT:CIT;DOS:DOS;CMT:CMT;BDP:BDP';//MDN_Colissimo_Model_System_Config_DeliveryMode
        $customFieldsArray[] = $entry;

        $entry = array();
        $entry['f_code'] = 'regatecode';
        $entry['f_name'] = $this->__('Relay point');
        $entry['f_value'] = $this->getOrderRegate($order);
        $entry['f_type'] = 'input';
        $customFieldsArray[] = $entry;

        $entry = array();
        $entry['f_code'] = 'gabarit';
        $entry['f_name'] = $this->__('Hors Gabarit');
        $entry['f_value'] = $this->getDefaultGabarit($order);
        $entry['f_type'] = 'list';
        $entry['f_disabled'] = true;
        $entry['f_list_elements'] = '0:No;1:Yes';
        $customFieldsArray[] = $entry;

        return $this->_createCollectionFromArray($customFieldsArray);
    }



    /**
     * To implement a function that will create the package that need to be added into
     * the magento shipment object.
     */
    public function createPackages($shipment,$packageData)
    {
        $return = array();

        /**
         * Setting base values for packages
         */
        $packageID = 1;
        foreach ($packageData as $key => $packageInfo) {

            $package[$key] = array(
                'packageID' => $packageID,
                'weight' => $packageInfo['weight'],
                'deliverymode' => $packageInfo['deliverymode'],
                'regatecode' => $packageInfo['regatecode'],
                'shipmenttype' => $packageInfo['shipmenttype'],
                'parceltype' => $packageInfo['parceltype']
            );

            /**
             * Checking dimensions
             */

            if ($packageInfo['parceltype'] == 1) {
                if ($packageInfo['length'] != '' && $packageInfo['height'] != '' && $packageInfo['height'] != '') {
                    $package[$key]['length'] = $packageInfo['length'];
                    $package[$key]['width'] = $packageInfo['width'];
                    $package[$key]['height'] = $packageInfo['height'];
                }
            } else if ($packageInfo['parceltype'] == 2) {
                if ($packageInfo['length'] != '' && $packageInfo['diam'] != '') {
                    $package[$key]['length'] = $packageInfo['length'];
                    $package[$key]['diam'] = $packageInfo['diam'];
                }
            }


            /**
             * Adding products informations to package
             */
            if (isset($packageInfo['products'])) {
                foreach ($packageInfo['products'] as $product) {
                    $product = json_decode($product);

                    /**
                     * Calculating package value and filling package products
                     */
                    $totalPrice = 0;
                    $qty = (int)$product->qty_ordered;
                    $totalPrice += ($qty * $product->price);

                    $package[$key]['items'][$product->order_item_id] = array(
                        'qty' => $qty,
                        'customs_value' => $qty * $product->price,
                        'price' => $product->price,
                        'name' => $product->name,
                        'weight' => $product->weight,
                        'product_id' => $product->product_id,
                        'order_item_id' => $product->order_item_id,
                    );
                }

                $package[$key]['customs_value'] = $totalPrice;
            }
            $packageID++;
        }

        $shipment->setPackages($package);

        return $shipment;
    }

    public function getOrderRegate($order)
    {
        return $order->getsoco_relay_point_code();
    }

    public function getOrderDeliveryMode($order)
    {
        $orderDeliveryMode = $order->getsoco_product_code();
        if (!$orderDeliveryMode)
            $orderDeliveryMode = Mage::getStoreConfig('colissimo/config_shipment/deliverymode');
        return $orderDeliveryMode;
    }

    public function getDefaultWeight($order)
    {
        return Mage::helper('colissimo/Order')->getWeight($order);
    }

    public function getDefaultLength($order)
    {
        return Mage::getStoreConfig('colissimo/config_shipment/default_length');
    }

    public function getDefaultWidth($order)
    {
        return Mage::getStoreConfig('colissimo/config_shipment/default_width');
    }

    public function getDefaultHeight($order)
    {
        return Mage::getStoreConfig('colissimo/config_shipment/default_height');
    }

    public function getDefaultDiam($order)
    {
        return 1;
        //TODO IN COLISSIMO EXT - Option does not exist
        //return  Mage::getStoreConfig('colissimo/config_shipment/default_diameter');
    }

    public function getDefaultCategorie($order)
    {
        return Mage::getStoreConfig('colissimo/config_shipment/categorie');
    }

    public function getDefaultParcelType($order)
    {
        return Mage::getStoreConfig('colissimo/config_shipment/parcel_type');
    }

    public function getDefaultGabarit($order)
    {
        return 0;
        //return  Mage::getStoreConfig('colissimo/config_shipment/mecanisable');
    }

    public function getDefaultDimensionUnit(){

        $unit = 'cm';
        //$unit = $package->getDimensionUnits();
        //return  Mage::helper('usa')->getMeasureDimensionName($unit);
        return $unit;

    }

    public function getDefaultWeightUnit(){

        $unit = 'kg';
        //$unit = $package->getWeightUnits();
        //return  Mage::helper('usa')->getMeasureWeightName($unit);
        return $unit;
    }





}