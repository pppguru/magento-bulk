<?php

class MDN_ProductReturn_Block_Widget_Grid_Column_Filter_ProductReturnProducts extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{

    public function getCondition()
    {
        $searchString = $this->getValue();

        $productSelect = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter(array(
                array('attribute' => 'name', array('like' => '%' . $searchString . '%')),
                array('attribute' => 'sku', array('like' => '%' . $searchString . '%'))
            ));
        $productIds    = $productSelect->getAllIds();

        $rmaSelect = Mage::getModel('ProductReturn/RmaProducts')->getCollection()
            ->addFieldToSelect('rp_rma_id')
            ->addFieldToFilter('rp_product_id', $productIds)
            ->addFieldToFilter('rp_qty', array(array('gt' => '0')));
        $rmaIds    = $rmaSelect->toArray();

        return array('in' => $rmaIds['items']);
    }

}