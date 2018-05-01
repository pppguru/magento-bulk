<?php

/**
 * Class HelpButton
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class MDN_MyBms_Block_Popup extends Mage_Adminhtml_Block_Abstract
{
    /**
     * @return bool
     */
    public function canShowPopup()
    {
        return Mage::Helper('MyBms/Popup')->canShow();
    }
}