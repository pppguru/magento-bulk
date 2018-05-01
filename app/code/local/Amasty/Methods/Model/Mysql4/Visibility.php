<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */
class Amasty_Methods_Model_Mysql4_Visibility extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('ammethods/visibility', 'entity_id');
    }
    
    public function loadVisibility(Mage_Core_Model_Abstract $object, $type, $websiteId, $methodCode)
    {
        $read = $this->_getReadAdapter();
        if ($read) {
            
            $select = $this->_getReadAdapter()->select()
                           ->from($this->getMainTable())
                           ->where($this->getMainTable().'.type=?', $type)
                           ->where($this->getMainTable().'.website_id=?', $websiteId)
                           ->where($this->getMainTable().'.method=?', $methodCode);
                           
            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->_afterLoad($object);

        return $this;
    }
}