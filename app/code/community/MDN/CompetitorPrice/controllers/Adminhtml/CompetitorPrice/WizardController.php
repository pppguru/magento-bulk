<?php

class MDN_CompetitorPrice_Adminhtml_CompetitorPrice_WizardController extends Mage_Adminhtml_Controller_Action
{
    public function SaveAction()
    {
        $data = $this->getRequest()->getPost('competitorPrice');

        $config = Mage::getModel('core/config');
        if (isset($data['attribute']))
            $config->saveConfig('competitorprice/general/barcode_attribute', $data['attribute']);
        $config->saveConfig('competitorprice/general/gs_website', $data['country']);
        $config->saveConfig('competitorprice/general/wizarded', 1);
        $config->cleanCache();

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Price Tracker is now configured, you can check results in the Price Tracker column below'));
        $this->_redirect('adminhtml/catalog_product/index');
    }

    protected function _isAllowed()
    {
        return true;
    }

}