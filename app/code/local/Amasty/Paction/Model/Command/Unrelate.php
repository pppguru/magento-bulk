<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2011 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/
class Amasty_Paction_Model_Command_Unrelate extends Amasty_Paction_Model_Command_Abstract 
{ 
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label = 'Remove Relations';
    }
    
    public function getLinkType()
    {
        $types = array(
            'uncrosssell' => Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL,
            'unupsell'    => Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL,
            'unrelate'    => Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED,
        );
        return $types[$this->_type];
    }
    
    public function isSelectedFromAll()
    {
        return (Mage::getStoreConfig('ampaction/unlink/' . $this->_type) == 1);
    }
    
    public function isBetweenSelected()
    {
        return (!Mage::getStoreConfig('ampaction/unlink/' . $this->_type));
    }
    
    public function toOptionArray()
    {
        $options = array(
            array('value'=> 0, 'label' => Mage::helper('ampaction')->__('Remove relations between selected products only')),
            array('value'=> 1, 'label' => Mage::helper('ampaction')->__('Remove selected products from ALL relations in the catalog')),
            array('value'=> 2, 'label' => Mage::helper('ampaction')->__('Remove all relations from selected products')),
        );
        
        return $options;
    }
    
    /**
     * Executes the command
     *
     * @param array $ids product ids
     * @param int $storeId store id
     * @param string $val field value
     * @return string success message if any
     */    
    public function execute($ids, $storeId, $val)
    {
        $this->_errors = array();
        
        $hlp = Mage::helper('ampaction');
        if (!is_array($ids)) {
            throw new Exception($hlp->__('Please select product(s)')); 
        }
        
        $db    = Mage::getSingleton('core/resource')->getConnection('core_write');  
        $table = Mage::getSingleton('core/resource')->getTableName('catalog/product_link');
        
        if ($this->isBetweenSelected()) {
            $where = array(
                'product_id IN(?)'        => $ids,
                'linked_product_id IN(?)' => $ids,
            );
        } elseif ($this->isSelectedFromAll()) {
            $where = array(
                'linked_product_id IN(?)' => $ids,
            );
        } else { // Remove all relations from selected products
            $where = array(
                'product_id IN(?)' => $ids,
            );
        }
        
        $db->delete($table, array_merge($where, array('link_type_id = ?' => $this->getLinkType())));
        
        $success = $hlp->__('Product associations have been successfully deleted.');
        
        return $success;
    }
    
    /**
     * Returns value field options for the mass actions block
     *
     * @param string $title field title
     * @return array
     */
    protected function _getValueField($title)
    {
        $title = $title; // prevents Zend Studio validtaion error
        return null;       
    }
}
