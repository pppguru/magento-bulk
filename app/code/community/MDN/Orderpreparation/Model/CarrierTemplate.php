<?php

class MDN_Orderpreparation_Model_CarrierTemplate extends Mage_Core_Model_Abstract {

    private $_fields = null;
    private $_customFields = null;
    private $_myData = null;

    public function _construct() {
        parent::_construct();
        $this->_init('Orderpreparation/CarrierTemplate');
    }

    /**
     * Return fields
     *
     * @param unknown_type $type
     * @return unknown
     */
    public function getFields($type = null) {
        $collection = mage::getModel('Orderpreparation/CarrierTemplateField')
                        ->getCollection()
                        ->addFieldToFilter('ctf_template_id', $this->getId());
        if ($type != null)
            $collection->addFieldToFilter('ctf_type', $type);
        $collection->setOrder('ctf_position', 'asc');
        $this->_fields = $collection;

        return $this->_fields;
    }

    /**
     * Create export file
     *
     * @param unknown_type $orderPreparationCollection
     */
    public function createExportFile($orderPreparationCollection) {
        $carrierTemplateOutput = '';

        //if specific type set
        if ($this->getct_type() != 'manual') {
            return mage::helper('Orderpreparation/SpecificCarrierTemplates_' . $this->getct_type())->createExportFile($orderPreparationCollection);
        }

        $FD = $this->getFieldDelimiter('export');
        $FS = $this->getFieldSeparator('export');
        $LD = $this->getLineDelimiter();

        //add header
        if ($this->getct_export_add_header() == 1) {
            $header = '';
            foreach ($this->getFields('export') as $field) {
                $header .= $FD . $field->getctf_name() . $FD . $FS;
            }
            $carrierTemplateOutput .= substr($header, 0, strlen($header) - strlen($FS)).$LD;
        } else {
            if ($this->getct_export_custom_header() != '') {
                $carrierTemplateOutput .= $this->getct_export_custom_header().$LD;
            }
        }

        //add orders
        foreach ($orderPreparationCollection as $orderToPrepare) {
            if (!$this->isOrderShippingMethodMatches($orderToPrepare->GetOrder()))
                continue;

            $line = '';
            $currentData = $this->getDataArray($orderToPrepare);
            foreach ($this->getFields('export') as $field) {
                $field->setParentTemplate($this);
                $line .= $FD . $field->getValue($currentData) . $FD . $FS;
            }
            $carrierTemplateOutput .= substr($line, 0, strlen($line) - strlen($FS)).$LD;
        }

        return $carrierTemplateOutput;
    }

    /**
     * Import tracking file
     *
     */
    public function importTrackingFile($t_lines) {
        $importedTrackingCount = 0;
        $skippedTrackingCount = 0;
        $debug = '';
        $trackingLabel = $this->getct_tracking_label();

        //if specific type set
        if ($this->getct_type() != 'manual') {
            $helperName = 'Orderpreparation/SpecificCarrierTemplates_' . $this->getct_type();
            return mage::helper($helperName)->importTrackingFile($t_lines);
        }
        
        $debug .= ', '.count($t_lines).' lines in the file';
        for ($i = 0; $i < count($t_lines); $i++) {
            //skip first line (if required)
            $line = $t_lines[$i];
            if (($i == 0) && ($this->getct_import_skip_first_record()))
                continue;

            $tracking = null;
            $shipmentReference = null;

            //parse fixed format line
            if ($this->getct_import_format() == 'fixed') {
                $currentPosition = 0;

                foreach ($this->getFields('import') as $field) {
                    $size = $field->getctf_size();
                    $fieldValue = substr($line, $currentPosition, $size);

                    switch ($field->getctf_content()) {
                        case 'tracking':
                            $tracking = trim($fieldValue);
                            break;
                        case 'shipment':
                            $shipmentReference = trim($fieldValue);
                            break;
                    }

                    $currentPosition += $size;
                }
            }

            //parse delimiter format line
            if ($this->getct_import_format() == 'delimiter') {
                //split fields
                $t_columns = explode($this->getFieldSeparator('import'), $line);
                $debug .= 'colonne_count='.count($t_columns);
                foreach ($this->getFields('import') as $field) {
                    if (isset($t_columns[$field->getctf_position()])) {
                        $fieldValue = $t_columns[$field->getctf_position()];
                        $debug .= ' fieldValue='.$fieldValue;
                        if ($this->getFieldDelimiter('import') != '')
                            $fieldValue = str_replace($this->getFieldDelimiter('import'), '', $fieldValue);

                        switch ($field->getctf_content()) {
                            case 'tracking':
                                $tracking = $fieldValue;
                                break;
                            case 'shipment':
                                $shipmentReference = $fieldValue;
                                break;
                        }
                        $debug .= ' content='.$field->getctf_content();

                    }
                }
            }

            //add tracking
            if (($tracking != null) && ($shipmentReference != null)) {
                $result = mage::helper('Orderpreparation/Tracking')->addTrackingToShipment($tracking, $shipmentReference, $this->getct_shipping_method(), $trackingLabel);
                if ($result)
                    $importedTrackingCount++;
                else
                    $skippedTrackingCount++;
            }
            else
                $debug .= 'Unable to retrieve shipment or/and tracking for line #' . $i . "\n";
        }

        //mage::log($debug);
        $msg = mage::helper('Orderpreparation')->__('Tracking import complete : %s tracking imported, %s tracking skipped : DEBUG : %s', $importedTrackingCount, $skippedTrackingCount, $debug);
        return $msg;
    }

    /**
     * Create an array with every data that can be used in field content
     *
     * @param unknown_type $orderToPrepare
     */
    public function getDataArray($orderToPrepare) {
        $this->_myData = array();
        $order = mage::getModel('sales/order')->load($orderToPrepare->getorder_id());
        $shipment = mage::getModel('sales/order_shipment')->loadByIncrementId($orderToPrepare->getshipment_id());
        $address = $order->getShippingAddress();
        if (!$order->getShippingAddress())
            $address = $order->getBillingAddress();

        //misc
        $this->_myData['line_return'] = "\r\n";

        //customer information
        $this->_myData['cust_ref'] = $order->getcustomer_id();
        $this->_myData['cust_ref2'] = str_replace(' ', '', strtoupper($address->getFirstname() . substr($address->getLastname(), 0, 1)));
        
        $this->_myData['prefix'] = $address->getprefix();
        $this->_myData['company'] = $address->getcompany();
        $this->_myData['firstname'] = $address->getfirstname();
        $this->_myData['lastname'] = $address->getlastname();
        $this->_myData['email'] = $order->getCustomerEmail();

        //address
        $this->_myData['street1'] = $address->getStreet(1);
        $this->_myData['street2'] = $address->getStreet(2);
        
        $this->_myData['street3'] = $address->getStreet(3);
        $this->_myData['region'] = $address->getregion();
        $this->_myData['country'] = $address->getCountryModel()->getName();
        $this->_myData['country_code'] = $address->getCountry();
        $this->_myData['postcode'] = $address->getPostcode();
        $this->_myData['city'] = $address->getcity();
        $this->_myData['telephone'] = $address->gettelephone();

        //shipment & order
        $this->_myData['order_ref'] = $order->getincrement_id();
        $this->_myData['order_date'] = $order->getcreated_at();
        $this->_myData['shipment_ref'] = $shipment->getincrement_id();
        $this->_myData['shipment_date'] = $shipment->getcreated_at();
        $this->_myData['order_total'] = $order->getbase_grand_total();
        $this->_myData['weight'] = $orderToPrepare->getreal_weight();
        $this->_myData['package_count'] = $orderToPrepare->getpackage_count();

        //region code
        $regionId = $address->getregion_id();
        $region = mage::getModel('directory/region')->load($regionId);
        $this->_myData['region_code'] = $region->getcode();

        //Instructions
        $tels = $address->gettelephone();
        if ($address->getmobile() != '')
            $tels .= ',' . $address->getmobile();
        $instruction = "";
        if ($address->getbuilding() != "")
            $instruction .= "Bat " . $address->getbuilding();
        if ($address->getdoor_code() != "")
            $instruction .= "-Digi " . $address->getdoor_code();
        if ($address->getfloor() != "")
            $instruction .= "-Etg " . $address->getfloor();
        if ($address->getappartment() != "")
            $instruction .= "-Apt " . $address->getappartment();
        $this->_myData['instructions'] = $instruction . ',' . $tels;


        //specif so colissimo
        $this->_myData['soco_product_code'] = $order->getsoco_product_code();
        if (!$this->_myData['soco_product_code'])
            $this->_myData['soco_product_code'] = 'DOM';
        $this->_myData['soco_shipping_instruction'] = $order->getsoco_shipping_instruction();
        $this->_myData['soco_door_code1'] = $order->getsoco_door_code1();
        $this->_myData['soco_door_code2'] = $order->getsoco_door_code2();
        $this->_myData['soco_interphone'] = $order->getsoco_interphone();
        $this->_myData['soco_relay_point_code'] = $order->getsoco_relay_point_code();
        $this->_myData['soco_civility'] = $order->getsoco_civility();
        $this->_myData['soco_phone_number'] = $order->getsoco_phone_number();
        $this->_myData['soco_email'] = $order->getsoco_email();
        $this->_myData['order_subtotal_ht'] = $order->getbase_subtotal();

        //add custom fields
        $value = $orderToPrepare->getcustom_values();
        $rows = explode(';', $value);
        foreach ($rows as $row) {
            if($row){
              $fields = explode('=', $row);
              if (count($fields) == 2){
                  $this->_myData[$fields[0]] = $fields[1];
              }
            }
        }

        //add all the field possible from the order, the adresses and the payment
        $sources = array('order_' => $order, 'address_' => $address, 'payment_' => $order->getPayment());
        foreach($sources as $prefix => $object) {
          if($object){
            foreach($object->getData() as $key => $value) {
                if($key){
                  $itemKey = $prefix . $key;
                  if (!isset($this->_myData[$itemKey])) {
                    $this->_myData[$itemKey] = $value;
                  }
                }
            }
          }
        }
        
        return $this->_myData;
    }

    public function getUsableCodes() {
        $retour = array();

        $retour[] = 'cust_ref';
        $retour[] = 'cust_ref2';
        $retour[] = 'prefix';
        $retour[] = 'company';
        $retour[] = 'firstname';
        $retour[] = 'lastname';
        $retour[] = 'email';

        $retour[] = 'street1';
        $retour[] = 'street2';
        $retour[] = 'street3';
        $retour[] = 'region';
        $retour[] = 'region_code';
        $retour[] = 'country';
        $retour[] = 'country_code';
        $retour[] = 'postcode';
        $retour[] = 'city';
        $retour[] = 'telephone';

        $retour[] = 'order_ref';
        $retour[] = 'order_date';
        $retour[] = 'shipment_ref';
        $retour[] = 'shipment_date';
        $retour[] = 'order_total';
        $retour[] = 'weight';

        $retour[] = 'instructions';

        //add customer fields
        foreach ($this->getCustomFields() as $field) {
            $retour[] = $field->getCode();
        }

        try
        {
          $order = mage::getModel('sales/order')->load(1); //need at least 1 order
          $address = $order->getShippingAddress();
          if (!$order->getShippingAddress())
              $address = $order->getBillingAddress();

          //add all the field possible from the order, the adresses and the payment
          $sources = array('order_' => $order, 'address_' => $address, 'payment_' => $order->getPayment());
          foreach($sources as $prefix => $object) {
            if($object){
              foreach($object->getData() as $key => $value) {
                  if($key){
                    $itemKey = $prefix . $key;
                    $retour[] = $itemKey;
                  }
              }
            }
          }
        }catch(Exception $ex){
          //ignore
        }
        
        return $retour;
    }

    /**
     * return char separator between fields for export file
     *
     * @return unknown
     */
    private function getFieldSeparator($type) {
        $code = '';
        if ($type == 'import')
            $code = $this->getct_import_file_separator();
        else
            $code = $this->getct_export_file_separator();

        switch ($code) {
            case 'coma':
                return ',';
                break;
            case 'pipe':
                return '|';
                break;
            case 'semicolon':
                return ';';
                break;
            case 'tab':
                return chr(9);
                break;
        }
    }

    /**
     * return char separator between fields for export file
     *
     * @return unknown
     */
    private function getFieldDelimiter($type) {
        $code = '';
        if ($type == 'import')
            $code = $this->getct_import_file_delimiter();
        else
            $code = $this->getct_export_file_delimiter();

        switch ($code) {
            case 'quote':
                return "'";
                break;
            case 'doublequote':
                return '"';
                break;
        }
    }

    /**
     * Return char to end line
     *
     * @return unknown
     */
    private function getLineDelimiter() {
        switch ($this->getct_export_line_end()) {
            case 'n':
                return chr(13);
                break;
            case 'r':
                return chr(10);
                break;
            case 'rn':
                return chr(13) . chr(10);
                break;
        }
    }

    /**
     * return custom fields
     *
     */
    public function getCustomFields() {
        if ($this->_customFields == null) {
            $this->_customFields = array();
            foreach ($this->getFields('export') as $field) {
                if ($field->isCustomField())
                    $this->_customFields[] = $field;
            }
        }
        return $this->_customFields;
    }

    /**
     *
     *
     * @param unknown_type $order
     */
    public function isOrderShippingMethodMatches($order) {
        $shippingMethod = $order->getshipping_method();
        $pos = false;
        if($this->getct_shipping_method() != null){
          $pos = strpos($shippingMethod, $this->getct_shipping_method());
        }
        if ($pos === false)
            return false;
        else
            return true;
    }

    /**
     * Return client directory name to automatically print shipping label
     *
     * @return unknown
     */
    public function getClientDirectoryName() {
        return 'directory_' . $this->getct_shipping_method();
    }

    public function getFileName() {
        return $this->getct_export_filename();
    }

    /**
     *
     */
    public function getForm($orderToPrepare, $prefix = null)
    {
        //manage specific carrier template
        if ($this->getct_type() != 'manual') {
            $orderId = $orderToPrepare->getorder_id();
            if ($orderId > 0) {
                $order = mage::getModel('sales/order')->load($orderId);
                $html = mage::helper('Orderpreparation/SpecificCarrierTemplates_' . $this->getct_type())->getForm($order);
            }
        }else{
            $html = $this->getManualCarrierTemplateForm($orderToPrepare, $prefix);
        }
        return $html;
    }

    protected function getManualCarrierTemplateForm($orderToPrepare, $prefix = null)
    {
        if ($prefix == null)
            $prefix = 'data[' . $orderToPrepare->getorder_id() . '][custom_values]';

        $html = '<table>';
        foreach ($this->getCustomFields() as $customField)
        {
            $html .= '<tr>';
            $html .= '<td align="left"><b>'.$customField->getctf_name().'</b> : </td>';
            $html .= '<td align="left">'.$customField->getCustomFieldControl($this->getDataArray($orderToPrepare), $prefix).'</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }

}