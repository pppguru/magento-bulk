<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */
class Amasty_Methods_Model_Visibility extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ammethods/visibility');
    }
    
    public function loadVisibility($type, $websiteId, $methodCode)
    {
        $this->_getResource()->loadVisibility($this, $type, $websiteId, $methodCode);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }
}