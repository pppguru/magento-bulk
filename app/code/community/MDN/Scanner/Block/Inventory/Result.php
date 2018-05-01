<?php

class MDN_Scanner_Block_Inventory_Result extends Mage_Adminhtml_Block_Widget_Form {

    private $_collection = null;
    private $_product = null;
    private $_keyword = null;

    public function getQueryString() {
        return $this->_keyword;
    }

    /**
     * Initialise la collection
     *
     * @param unknown_type $keyword
     */
    public function initResult($keyword) {
        $this->_keyword = $keyword;

        //perform search using barcode first
        $this->_product = mage::helper('AdvancedStock/Product_Barcode')->getProductFromBarcode($keyword);

        //if not found, make a search
        if ($this->_product == null) {
            $this->_collection = mage::getModel('catalog/product')
                            ->getCollection()
                            ->addAttributeToSelect('name')
                            ->addExpressionAttributeToSelect('name_plus_sku',
                                    'CONCAT({{name}}, " ", {{sku}})',
                                    array('name', 'sku'))
                            ->addAttributeToFilter('name_plus_sku', array('like' => '%' . $keyword . '%'));
        }
    }

    /**
     * D�finit si un seul produit correspond
     *
     * @return unknown
     */
    public function hasOnlyOneResult() {
        if ($this->_product != null)
            return true;
        else {
            return ($this->getCollection()->getSize() == 1);
        }
    }

    /**
     * retourne tous les r�sultats
     *
     * @return unknown
     */
    public function getCollection() {
        return $this->_collection;
    }

    /**
     * retourne le seul produit
     *
     * @return unknown
     */
    public function getOnlyProduct() {
        if ($this->_product != null)
            return $this->_product;
        else {
            foreach ($this->getCollection() as $item)
                return $item;
        }
    }

}