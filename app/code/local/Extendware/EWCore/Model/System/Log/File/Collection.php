<?php

class Extendware_EWCore_Model_System_Log_File_Collection extends Extendware_EWCore_Model_Varien_Data_Collection 
{
	public function setCurPage($page)
    {
       	parent::setCurPage($page);
       	
       	if ($this->getCurPage() > 0 and $this->getPageSize() > 0) {
       		$this->_items = array_slice($this->_items, ($this->getCurPage() - 1)*$this->getPageSize(), $this->getPageSize());
       	}
        return $this;
    }
}