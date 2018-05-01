<?php

class MDN_Purchase_Block_Widget_Column_Filter_SupplyNeeds_Suppliers extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select {

    protected function _getOptions() {
        return $this->getSuppliersAsArray();
    }

    public function getCondition() {

        $productIds = $this->getProductIds($this->getValue());

        if ($this->getValue()) {
            return array('in' => $productIds);
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

    /**
     * return product ids for supplier
     * @param <type> $supplierId
     * @return <type>
     */
    protected function getProductIds($supplierId)
    {
        $productIds = Mage::getResourceModel('Purchase/ProductSupplier')->getProductIdsForSupplier($supplierId);
        return $productIds;
    }

}