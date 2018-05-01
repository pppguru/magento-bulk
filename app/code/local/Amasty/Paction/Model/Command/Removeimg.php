<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/
class Amasty_Paction_Model_Command_Removeimg extends Amasty_Paction_Model_Command_Copyimg 
{ 
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label = 'Remove Images';
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
        $hlp = Mage::helper('ampaction');
        
        if (!is_array($ids)) {
            throw new Exception($hlp->__('Please select product(s)')); 
        }
        
        // we do not use store id as it is a global action;
        foreach ($ids as $id) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($storeId)
                ->load($id);
            $attribute = $product->getResource()->getAttribute('media_gallery');
            $this->_removeData($attribute->getId(), $id, $product);
        }        
        
        $success = $hlp->__('Images and labels have been successfully deleted.');
        
        return $success; 
    }
    
    protected function _removeData($attrId, $productId, $product)
    {
        $countPic = 3;
        $db = Mage::getSingleton('core/resource')->getConnection('core_write'); 
        $table = Mage::getSingleton('core/resource')->getTableName('catalog/product_attribute_media_gallery');
        $varcharTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');
        $imageAttr = array(0 => 'image', 1 => 'small_image', 2 => 'thumbnail');
        
        for ($i = 0; $i < $countPic; $i++){
            $attributePic[$i] = $product->getResource()->getAttribute($imageAttr[$i]);
        }
        
        // Delete varchar
        for ($i = 0; $i < 3; $i++) {
            $db->delete($varcharTable, 'attribute_id = ' . $attributePic[$i]->getId() . ' AND entity_id = ' . $productId);
        }
        
        $valueIds = array();
        // Delete files
        $select = $db->select()
            ->from($table, array('value_id', 'value'))
            ->where('attribute_id = ?', $attrId)
            ->where('entity_id = ?', $productId);
        foreach ($db->fetchAll($select) as $row) {
            $path = Mage::getBaseDir('media') . DS . $this->_getConfig()->getMediaShortUrl($row['value']);
            if (file_exists($path)) {
                unlink($path);
            }
            $valueIds[] = $row['value_id'];
        }
        
        // Delete media
        $db->delete($table, 'attribute_id = ' . $attrId . ' AND entity_id = ' . $productId);

        // Delete labels
        $tableLabels = Mage::getSingleton('core/resource')->getTableName('catalog/product_attribute_media_gallery_value');
        $db->delete($tableLabels, $db->quoteInto('value_id IN(?)', $valueIds));
        
        return true;
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
