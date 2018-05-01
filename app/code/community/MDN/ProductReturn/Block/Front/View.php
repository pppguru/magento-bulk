<?php

class MDN_ProductReturn_Block_Front_View extends Mage_Core_Block_Template
{
    private $_productReturn = null;

    public function getRma()
    {
        if ($this->_productReturn == null) {
            $productReturnId      = $this->getRequest()->getParam('rma_id');
            $this->_productReturn = mage::getModel('ProductReturn/Rma')->load($productReturnId);
        }

        return $this->_productReturn;
    }

    /**
     * return current customer
     *
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    public function getCustomerAddressesAsCombo($name, $value)
    {
        return Mage::Helper('ProductReturn/Customer_Address')->getAddressesAsCombo($this->getRma(), $name, $value);
    }

    public function getCustomerAddressesAsText($value)
    {
        return Mage::Helper('ProductReturn/Customer_Address')->getAddressesAsTxt($this->getRma(), $value);
    }

    public function getCustomerAddresses($value)
    {
        $addresses = $this->getCustomer()->getAddresses();
        $retour    = "";
        foreach ($addresses as $address) {
            if ($value == $address->getId())
                $retour = $address->getFormated();
        }

        return $retour;
    }

    public function getReasonsAsCombo($name, $value)
    {
        $retour  = '<select name="' . $name . '" id="' . $name . '">';
        $reasons = $this->getRma()->getReasons(Mage::app()->getStore()->getStoreId());
        foreach ($reasons as $reason) {
            $selected = '';
            if ($value == $reason)
                $selected = ' selected="selected" ';
            $retour .= '<option value="' . $reason . '" ' . $selected . '>' . $this->__($reason) . '</option>';
        }

        $retour .= '</select>';

        return $retour;

    }

    public function getQtySelect($name, $max)
    {
        $retour = '<select name="' . $name . '" id="' . $name . '">';
        for ($i = 0; $i <= $max; $i++) {
            $retour .= '<option value="' . $i . '">' . $i . '</option>';
        }
        $retour .= '</select>';

        return $retour;
    }

    public function getReturnUrl()
    {
        return $this->getUrl('ProductReturn/Front/List');
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('ProductReturn/Front/SubmitRequest');
    }

    public function getReturnCGVUrl()
    {
        return $this->getUrl('ProductReturn/Front/ViewCGV', array('rma_id' => $this->getRma()->getrma_id()));
    }

    public function CustomerCanEdit()
    {
        return $this->getRma()->CustomerCanEdit();
    }

    /**
     * Enter description here...
     *
     */
    public function getProductName($product)
    {
        return mage::getModel('ProductReturn/RmaProducts')->getProductName($product);
    }
}