<?php

class MDN_Purchase_Block_Widget_Column_Filter_ProductSupplierSku extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text {

    public function getCondition() {
        if ($this->getValue())
        {
            //get product ids matching to the reference
            $collection = mage::getModel('Purchase/ProductSupplier')
                    ->getCollection()
                    ->addFieldToFilter('pps_reference', array('like' => '%'.$this->getValue().'%'));
            $ids = array();
            foreach ($collection as $item) {
                $ids[] = $item->getpps_product_id();
            }

            return array('in' => $ids);
        }
    }

}