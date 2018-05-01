<?php
class Extendware_EWCore_Block_Core_Static extends Extendware_EWCore_Block_Mage_Core_Abstract
{
	protected function _toHtml()
    {
        return $this->getContent();
    }
}
