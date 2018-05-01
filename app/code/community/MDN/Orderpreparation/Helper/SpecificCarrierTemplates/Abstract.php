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
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract extends Mage_Core_Helper_Abstract {

    public function createExportFile($orderPreparationCollection) {
        throw new Exception('Implement export file for specific carrier template');
    }

    public function importTrackingFile($t_lines) {
        throw new Exception('Implement import file for specific carrier template');
    }

    protected function getAddress($order) {
        $address = $order->getShippingAddress();
        if (!$address)
            $address = $order->getBillingAddress();
        return $address;
    }

    /**
     * To implement the form that could be displayed in the packing screen of ERP.
     *
     * @return ''
     */
    public function getForm($order){
        return '';
    }

    /**
     * To implement a function that will create the package that need to be added into
     * the magento shipment object.
     */
    public function createPackages($shipment,$packageData){
       return null;
    }



    protected function _createCollectionFromArray($customFieldsArray){
        $collection = new Varien_Data_Collection();
        foreach($customFieldsArray as $customFieldEntry){
            $item = new Varien_Object();
            foreach($customFieldEntry as $key => $value) {
                $item->setData($key, $value);
            }
            $collection->addItem($item);
        }
        return $collection;
    }

    protected function getFormCarrierTemplateKey(){
        return 'carriertemplatedata';
    }

    protected function getFields($order){
        return new Varien_Data_Collection();
    }

    protected function getStandardFormField($order, $nbOfColumns = 1){
        $fieldCollection = $this->getFields($order);
        $nbFieldsToDisplay = $fieldCollection->getSize();
        $nbOfLines = 1;

        $html = '';

        if($nbFieldsToDisplay > 0) {

            if($nbOfColumns > 0) {
                $nbOfLines = (int)$nbFieldsToDisplay / $nbOfColumns;
            }

            $html .= '<table>';
            $lineCount=1;
            $columnsCount = 0;
            foreach ($fieldCollection as $field) {

                if (($lineCount<$nbOfLines) && (($columnsCount == 0) || ($columnsCount == $nbOfColumns))) {
                    $html .= '<tr>';
                }
                    //label
                    $html .= '<td align="left"><b>' . $field->getf_name() . '</b></td>';
                    //field
                    $html .= '<td align="left">' . $this->getFieldControl($field) . '</td>';
                    $columnsCount ++;

                if (($lineCount<$nbOfLines) && (($columnsCount == 0) || ($columnsCount == $nbOfColumns))) {
                    $html .= '</tr>';
                    if($columnsCount == $nbOfColumns) {
                        $lineCount++;
                        $columnsCount = 0;
                    }
                }
            }
            $html .= '</table>';
        }
        return $html;
    }

    protected function getFieldControl($field) {
        $html = '';

        $fieldHtmlId =  $this->getFormCarrierTemplateKey().'[' . $field->getf_code() . ']';
        $disabled = ($field->getf_disabled())?'disabled':'';
        $onChange = ($field->getf_on_change())?' onchange="'.$field->getf_on_change().'" ':'';
        switch ($field->getf_type()) {
            case 'input':
                $html = '<input type="text" name="'.$fieldHtmlId.'" id="'.$fieldHtmlId.'" value="'.trim($field->getf_value()).'" '.$disabled.' '.$onChange.'>';
                break;
            case 'list':
                $html = '<select name="' . $fieldHtmlId . '" id="' . $fieldHtmlId . '" '.$disabled.' '.$onChange.'>';
                $listElements = explode(';', $field->getf_list_elements());
                foreach ($listElements as $element) {
                    $keyValue = explode(':', $element);
                    if (count($keyValue) == 2) {
                        $selected = '';
                        if ($field->getf_value() == $keyValue[0])
                            $selected = ' selected ';
                        $html .= '<option value="' . $keyValue[0] . '" ' . $selected . '>' . trim($keyValue[1]) . '</option>';
                    }
                }
                $html .= '</select>';
                break;
        }

        //after the field
        $html .= ' <b>'.(($field->getf_display_after())?$field->getf_display_after():'').'</b>  ';


        return $html;
    }

    /**
     * Transform a string into an key value array
     *
     * String Ex : weight=61;height=30;length=30;width=30;parceltype=1;shipmenttype=1;deliverymode=DOM;regatecode=BDP;
     *
     * @param $values
     * @return array
     */
    public function getPrepareCustomValuesAsPackageData($values, $elementSeparator = ';', $keyValueSeparator = '='){

        $packageData = array();
        $listElements = explode($elementSeparator, $values);
        foreach ($listElements as $element) {
            $keyValue = explode($keyValueSeparator, $element);
            if (count($keyValue) == 2 && $keyValue[0] != $this->getFormCarrierTemplateKey()) {
                $packageData[trim($keyValue[0])] = trim($keyValue[1]);
            }
        }
        return $packageData;
    }

}