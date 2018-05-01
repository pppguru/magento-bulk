<?php


class MDN_SmartReport_Block_Report_Type_Sku extends MDN_SmartReport_Block_Report_Type
{
    protected $_product = null;

    public function getGroup()
    {
        return 'product_detail';
    }

    public function getTitle()
    {
        return Mage::helper('SmartReport')->getName().' - '.$this->getProduct()->getName().' ('.$this->getProduct()->getSku().')';
    }

    public function getProduct()
    {
        if ($this->_product == null)
        {
            $productId = $this->getRequest()->getParam('id');
            if (!$productId)
                $productId = $this->getRequest()->getParam('product_id');
            $this->_product = Mage::getModel('catalog/product')->load($productId);
        }
        return $this->_product;
    }


    public function isFormLess()
    {
        return true;
    }

    public function getContainer()
    {
        return 'product_info_tabs_smart_report_content|advancedstock_product_tabs_tab_smartreport_content';
    }

    public function getAjaxUrl()
    {
        $params = array();
        $params['id'] = $this->getProduct()->getId();
        $params['period'] = '{period}';
        $params['date_from'] = '{date_from}';
        $params['date_to'] = '{date_to}';
        $params['group_by_date'] = '{group_by_date}';
        $params['sm_store'] = '{sm_store}';

        return $this->getUrl('adminhtml/SmartReport_Reports/SkuDetailAjax', $params);
    }

    public function getAjaxMode()
    {
        return true;
    }


    public function getAdditionalButtons()
    {
        $buttons = array();

        $url = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $this->getProduct()->getId()));
        $buttons[] = '<button  title="Apply" type="button" class="scalable" onclick="setLocation(\''.$url.'\')" style="margin-right: 20px;"><span><span><span>'.$this->__('Product view').'</span></span></span></button>';

        $url = Mage::helper('adminhtml')->getUrl('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $this->getProduct()->getId()));
        $buttons[] = '<button  title="Apply" type="button" class="scalable" onclick="setLocation(\''.$url.'\')" style="margin-right: 20px;"><span><span><span>'.$this->__('ERP view').'</span></span></span></button>';

        return $buttons;
    }

}
