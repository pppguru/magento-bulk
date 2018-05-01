<?php


class Collinsharper_Wiretransfer_Block_Standard_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('wiretransfer/standard/info.phtml');
    }
}