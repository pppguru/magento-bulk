<?php


class MDN_SmartReport_Block_Report_Type_Supplier extends MDN_SmartReport_Block_Report_Type
{
    protected $_supplier = null;

    public function getGroup()
    {
        return 'supplier_detail';
    }

    public function getTitle()
    {
        return Mage::helper('SmartReport')->getName().' - '.$this->__('Supplier').' ' . $this->getSupplier()->getSupName();
    }

    public function getSupplier()
    {
        if ($this->_supplier == null) {
            $supplierId = $this->getRequest()->getParam('supplier_id');
            $this->_supplier = Mage::getModel('Purchase/Supplier')->load($supplierId);
        }
        return $this->_supplier;
    }


    public function isFormLess()
    {
        return true;
    }

    public function getContainer()
    {
        return 'purchase_supplier_tabs_tab_smartreport_content';
    }

    public function getAjaxUrl()
    {
        $params = array();
        $params['id'] = $this->getSupplier()->getId();
        $params['period'] = '{period}';
        $params['date_from'] = '{date_from}';
        $params['date_to'] = '{date_to}';
        $params['group_by_date'] = '{group_by_date}';
        $params['sm_store'] = '{sm_store}';

        return $this->getUrl('adminhtml/SmartReport_Reports/SupplierDetailAjax', $params);
    }

    public function getAjaxMode()
    {
        return true;
    }

}
