<?php

/**
 * Class MDN_CompetitorPrice_Block_Grid_Column_Renderer_Reference
 *
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_CompetitorPrice_Block_Grid_Column_Renderer_Reference extends MDN_CompetitorPrice_Block_Grid_Column_Renderer_Abstract {

    /**
     * @param \Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {

        $productId = $row->getData($this->getColumn()->getIndex());
        $channel = $this->getChannel();
        $fieldName = $this->getFieldName();
        $value = $row->getmp_reference();

        if (!$channel || !Mage::helper('CompetitorPrice')->isConfigured()) {
            return 'Disabled';
        }

        $html = '';
        $html .= '<div id="competitor_price_'.$productId.'" class="competitor_price"></div>';
        $html .= "<script>competitorPriceObj.addProduct('".$productId."', '".$channel."', '".$fieldName."', '".$value."');</script>";

        return $html;
    }

    /**
     * @param int $id
     * @param string $channel
     * @param string $mode
     * @return null
     */
    public function getValue($id, $channel, $mode)
    {
        return null;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        $country = Mage::registry('mp_country');
        $countryCode = strtolower($country->getmpac_country_code());
        $organization = strtolower($country->getAssociatedAccount()->getmpa_mp());

        switch($countryCode){
            case 'gb':
                $countryCode = 'uk';
                break;
        }

        return $organization.'_'.$countryCode.'_default';
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return MDN_CompetitorPrice_Helper_Data::kModeChannelReference;
    }

}