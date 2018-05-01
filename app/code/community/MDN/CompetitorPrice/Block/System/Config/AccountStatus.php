<?php

class MDN_CompetitorPrice_Block_System_Config_AccountStatus extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $details = Mage::helper('CompetitorPrice')->getAccountDetails();

        if ($details['status'] == 'ERROR')
            $details['status'] = 'Unable to connect';

        $html = '';
        $html .= '<p>'.$this->__('Status').' : '.$details['status'].'</p>';
        if (isset($details['message']))
            $html .= '<p>'.$this->__('Message').' : '.$details['message'].'</p>';

        if ($details['status'] != 'OK')
            $html = '<font color="red">'.$html.'</font>';
        $html .= '<p>'.$this->__('Credits balance').' : '.$details['credits'].'</p>';
        $html .= '<p>&nbsp;</p><p>'.$this->__('Need to purchase new credits ? login on ').'<a href="https://www.boostmyshop.com/Bms/Front/Credits" target="_new">boostmyshop.com</a></p>';

        return $html;
    }
}