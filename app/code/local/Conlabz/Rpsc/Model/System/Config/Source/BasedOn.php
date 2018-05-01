<?php

/**
 * @package Conlabz_Rpsc
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Conlabz_Rpsc_Model_System_Config_Source_BasedOn
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Conlabz_Rpsc_Helper_Data::BASED_ON_BILLING,
                'label' => Mage::helper('rpsc')->__('Billing Address')
            ),
            array(
                'value' => Conlabz_Rpsc_Helper_Data::BASED_ON_SHIPPING,
                'label' => Mage::helper('rpsc')->__('Shipping Address')
            )
        );
    }
}
