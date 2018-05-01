<?php

/**
 * @package Conlabz_Rpsc
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Conlabz_Rpsc_Model_System_Config_Source_Accesscontrol
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'allow',
                'label' => Mage::helper('rpsc')->__('allow')
            ),
            array(
                'value' => 'deny',
                'label' => Mage::helper('rpsc')->__('deny')
            )
        );
    }
}
