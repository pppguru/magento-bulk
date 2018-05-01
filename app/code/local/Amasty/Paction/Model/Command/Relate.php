<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/
class Amasty_Paction_Model_Command_Relate extends Amasty_Paction_Model_Command_Abstract 
{ 
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label = 'Relate';
        
        $this->_fieldLabel = 'To';

        if ($this->isOneWay()){
            if (Mage::getStoreConfig('ampaction/links/' . $this->_type . '_reverse')) {
                $this->_fieldLabel = 'IDs to Selected'; // new option
            } 
            else {
                $this->_fieldLabel = 'Selected To IDs'; // old option
            }
        }

        if ($this->isMultiWay()) {
            $this->_fieldLabel = '';
        }
    }
    
    public function getLinkType()
    {
        $types = array(
            'crosssell' => Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL,
            'upsell'    => Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL,
            'relate'    => Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED,
        );
        return $types[$this->_type];
    }
    
    public function isMultiWay()
    {
        return (Mage::getStoreConfig('ampaction/links/' . $this->_type) == 2);
    }  
    
    public function isTwoWay()
    {
        return (Mage::getStoreConfig('ampaction/links/' . $this->_type) == 1);
    }       

    public function isOneWay()
    {
        return (!Mage::getStoreConfig('ampaction/links/' . $this->_type));
    }       

    
    public function toOptionArray()
    {
        $options = array(
            array('value'=> 0, 'label' => Mage::helper('ampaction')->__('Default')),
            array('value'=> 1, 'label' => Mage::helper('ampaction')->__('2 Way')),
            array('value'=> 2, 'label' => Mage::helper('ampaction')->__('Multi Way')),
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
        $success = '';
        $hlp = Mage::helper('ampaction');

        if (!$this->isMultiWay()) {
            $success = parent::execute($ids, $storeId, $val);
        } elseif (!is_array($ids)) {
            throw new Exception($hlp->__('Please select product(s)')); 
        }
        
        
        $vals = array();
        if (!$this->isMultiWay()) {
            $vals = explode(',', $val);
            foreach ($vals as $val) {
                $mainId = intVal(trim($val));
                if (!$mainId) {
                    throw new Exception($hlp->__('Please provide a valid product ID'));
                }
            }
        }
        
        $num = 0;
            
        if ($this->isTwoWay()) {
            foreach ($vals as $mainId) {
                foreach ($ids as $id) {
                    $num += $this->_createNewLink($mainId, $id);
                    $num += $this->_createNewLink($id, $mainId);
                }
            }            
        } elseif ($this->isMultiWay()) {
            foreach ($ids as $id) {
                foreach ($ids as $id2) {
                    if ($id == $id2){
                        continue;
                    }
                    $num += $this->_createNewLink($id, $id2);
                }
            }
        } else { // default one-way relation
            foreach ($vals as $mainId) {
                foreach ($ids as $id) {
                    if (Mage::getStoreConfig('ampaction/links/' . $this->_type . '_reverse')) {
                        $num += $this->_createNewLink($id, $mainId);
                    } else {
                        $num += $this->_createNewLink($mainId, $id);
                    }
                }
            }
        } 
        
        if ($num){
            if (1 == $num)
                $success = $hlp->__('Product association has been successfully added.');
            else {
                $success = $hlp->__('%d product associations have been successfully added.', $num);
            }
        }
        
        return $success; 
    }
    
    //@todo optimize, move to one "insert into()  values (), (), .. ON DUPLICATE IGNORE"
    protected function _createNewLink($productId, $linkedProductId)
    {
        $db     = Mage::getSingleton('core/resource')->getConnection('core_write');  
        $table  = Mage::getSingleton('core/resource')->getTableName('catalog/product_link'); 
        
        $select = $db->select()->from($table)
            ->where('link_type_id=?', $this->getLinkType())           
            ->where('product_id =?', $productId)           
            ->where('linked_product_id =?', $linkedProductId);
        $row = $db->fetchRow($select); 

        $insertedCnt = 0;
        if (!$row){
            $db->insert($table, array(
                'product_id'        => $productId,
                'linked_product_id' => $linkedProductId,
                'link_type_id'      => $this->getLinkType(),
            )); 
            $insertedCnt = 1;                   
        }
        
        return $insertedCnt;        
    }    
    
    /**
     * Returns value field options for the mass actions block
     *
     * @param string $title field title
     * @return array
     */
    protected function _getValueField($title)
    {
        if ($this->isMultiWay()) {
            $title = $title; // prevents Zend Studio validtaion error
            return null;
        } else {
            return parent::_getValueField($title);
        }
    }
}
