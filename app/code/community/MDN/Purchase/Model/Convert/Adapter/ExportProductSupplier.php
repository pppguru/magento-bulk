<?php

class MDN_Purchase_Model_Convert_Adapter_ExportProductSupplier extends Mage_Dataflow_Model_Convert_Container_Abstract {

    private $_collection = null;

    const k_lineReturn = "\r\n";

    /**
     * Load product collection Id(s)
     *
     */
    public function load() {
        $nameAttributeId = mage::getModel('Purchase/Constant')->GetProductNameAttributeId();

        $this->_collection = mage::getModel('Purchase/ProductSupplier')
                ->getCollection()
                ->join('Purchase/Supplier', 'sup_id=pps_supplier_num')
                ->join('catalog/product', 'pps_product_id=`catalog/product`.entity_id')
                ->join('AdvancedStock/CatalogProductVarchar', '`catalog/product`.entity_id=`AdvancedStock/CatalogProductVarchar`.entity_id and `AdvancedStock/CatalogProductVarchar`.store_id = 0 and `AdvancedStock/CatalogProductVarchar`.attribute_id = ' . mage::getModel('AdvancedStock/Constant')->GetProductNameAttributeId())
        ;

        $this->addException(Mage::helper('dataflow')->__('Loaded %s rows', $this->_collection->getSize()), Mage_Dataflow_Model_Convert_Exception::NOTICE);
    }

    /**
     * Enregistre
     *
     */
    public function save() {
        $this->load();

        $path = $this->getVar('path') . '/' . $this->getVar('filename');
        $f = fopen($path, 'w');
        $fields = $this->getFields();

        //add header
        $header = '';
        foreach ($fields as $field) {
            $header .= $field['label'] . ';';
        }
        fwrite($f, $header . self::k_lineReturn);

        //add orders
        foreach ($this->_collection as $item) {
            $line = '';
            foreach ($fields as $field) {
                $line .= $item->getData($field['field']) . ';';
            }
            fwrite($f, $line . self::k_lineReturn);
        }

        fclose($f);
        $this->addException(Mage::helper('dataflow')->__('Export saved in %s', $path), Mage_Dataflow_Model_Convert_Exception::NOTICE);
    }

    /**
     * return fields to export
     *
     */
    public function getFields() {
        $t = array();

        $t[] = array('label' => 'product_sku', 'field' => 'sku');
        $t[] = array('label' => 'product_name', 'field' => 'value');

        $t[] = array('label' => 'supplier_code', 'field' => 'sup_code');
        $t[] = array('label' => 'supplier_name', 'field' => 'sup_name');

        $t[] = array('label' => 'supplier_product_reference', 'field' => 'pps_reference');
        $t[] = array('label' => 'supplier_last_price', 'field' => 'pps_last_price');
        $t[] = array('label' => 'supplier_last_price_with_extended_costs', 'field' => 'pps_last_unit_price');
        $t[] = array('label' => 'supplier_last_price_supplier_currency', 'field' => 'pps_last_unit_price_supplier_currency');

        //$t[] = array('label' => 'supplier_last_order_date', 'field' => 'pps_last_order_date');//obsolete

        $t[] = array('label' => 'supplier_comments', 'field' => 'pps_comments');
        $t[] = array('label' => 'supplier_price_position', 'field' => 'pps_price_position');
        $t[] = array('label' => 'supplier_supplier_qty', 'field' => 'pps_quantity_product');
        $t[] = array('label' => 'supplier_can_dropship', 'field' => 'pps_can_dropship');

        $t[] = array('label' => 'supplier_discount_level', 'field' => 'pps_discount_level');
        $t[] = array('label' => 'supplier_supply_delay', 'field' => 'pps_supply_delay');

        $t[] = array('label' => 'supplier_supplier_product_name', 'field' => 'pps_product_name');


        return $t;
    }

}