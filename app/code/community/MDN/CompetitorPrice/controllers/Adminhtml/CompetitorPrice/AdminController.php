<?php

class MDN_CompetitorPrice_Adminhtml_CompetitorPrice_AdminController extends Mage_Adminhtml_Controller_Action
{
    public function FlushCacheAction()
    {
        try
        {
            Mage::getSingleton('CompetitorPrice/Product')->truncate();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('CompetitorPrice')->__('Cache flushed'));
        }
        catch(Exception $ex)
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('CompetitorPrice')->__('An error occured : %s', $ex->getMessage()));
        }

        $this->_redirect('adminhtml/system_config/edit', array('section' => 'competitorprice'));
    }

    public function CreateTableAction()
    {
        try
        {
            Mage::helper('CompetitorPrice/Db')->createTable();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('CompetitorPrice')->__('Table created'));
        }
        catch(Exception $ex)
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('CompetitorPrice')->__('An error occured : %s', $ex->getMessage()));
        }

        $this->_redirect('adminhtml/system_config/edit', array('section' => 'competitorprice'));
    }

    protected function _isAllowed()
    {
        return true;
    }

}
