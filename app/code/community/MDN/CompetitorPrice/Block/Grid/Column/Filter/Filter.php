<?php

class MDN_CompetitorPrice_Block_Grid_Column_Filter_Filter
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract
{

    public function getHtml()
    {

        if (Mage::helper('CompetitorPrice')->isConfigured())
        {
            $html[] = 'Credits : <span class="credit_total"></span>';
            $html[] = '<span id="competitor_price_low_credits_warning" style="display: none; font-weight:normal;">Your credits are running low, reload from <a onclick="window.open(\'https://www.boostmyshop.com/Bms/Front/Credits\');" href="https://www.boostmyshop.com/Bms/Front/Credits/" target="_new">your customer account</a></span>';
        }
        else
        {
            $url = Mage::helper('adminhtml')->getUrl("adminhtml/system_config/edit", array('section' => 'competitorprice'));
            $html[] = '&nbsp;<br><input type="button" style="background-color: #ffffff; border: 1px solid black; padding: 3px; margin-top: 5px; width: 100px;" value="START" onclick="setLocation(\''.$url.'\')">';
        }

        return implode('<br>', $html);
    }

}