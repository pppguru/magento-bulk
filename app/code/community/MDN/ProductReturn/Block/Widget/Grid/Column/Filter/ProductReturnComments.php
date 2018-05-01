<?php

class MDN_ProductReturn_Block_Widget_Grid_Column_Filter_ProductReturnComments extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{

    public function getCondition()
    {
        $rmaIds    = array();
        $searchString = trim($this->getValue());

        if($searchString){

            $rmaSelect = Mage::getModel('ProductReturn/RmaProducts')->getCollection()
                ->addFieldToSelect('rp_rma_id')
                ->addFieldToFilter('rp_description', array('like' => '%'.$searchString.'%'));

            foreach ($rmaSelect as $rp ) {
                $rmaIds[]    = $rp->getrp_rma_id();
            }

            $rmaSelect2 = Mage::getModel('ProductReturn/Rma')->getCollection()
                ->addFieldToFilter('rma_private_description', array('like' => '%'.$searchString.'%'));

            foreach ($rmaSelect2 as $rma ) {
                if(!in_array($rma->getId(), $rmaIds)){
                    $rmaIds[]    = $rma->getId();
                }
            }

            $rmaSelect3 = Mage::getModel('ProductReturn/Rma')->getCollection()
                ->addFieldToFilter('rma_public_description', array('like' => '%'.$searchString.'%'));

            foreach ($rmaSelect3 as $rma ) {
                if(!in_array($rma->getId(), $rmaIds)){
                    $rmaIds[]    = $rma->getId();
                }
            }

        }

        return array('in' => $rmaIds);

    }

}