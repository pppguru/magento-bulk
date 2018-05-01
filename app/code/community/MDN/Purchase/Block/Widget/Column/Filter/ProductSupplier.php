<?php

class MDN_Purchase_Block_Widget_Column_Filter_ProductSupplier extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select {

    protected function _getOptions() {
        return $this->getSuppliersAsArray();
    }

    public function getCondition() {
        //retrieve products id associated to the supplier
        $supplierId = $this->getValue();
        $collection = mage::getModel('Purchase/ProductSupplier')
                ->getCollection()
                ->addFieldToFilter('pps_supplier_num', $supplierId);
        $ids = array();
        foreach ($collection as $item) {
            $ids[] = $item->getpps_product_id();
        }

        if ($this->getValue()) {
            return array('in' => $ids);
        }
    }

    /**
     * Return suppliers list as array
     *
     */
    public function getSuppliersAsArray() {
        $retour = array();
        $retour[] = array('label' => '', 'value' => '');

        //charge la liste des pays
        $collection = Mage::getModel('Purchase/Supplier')
                ->getCollection()
                ->setOrder('sup_name', 'asc');
        foreach ($collection as $item) {
            $retour[] = array('label' => $item->getsup_name(), 'value' => $item->getsup_id());
        }
        return $retour;
    }

}