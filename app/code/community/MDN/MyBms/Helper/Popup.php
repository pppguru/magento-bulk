<?php

/**
 * Class Popup
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class MDN_MyBms_Helper_popup extends Mage_Core_Helper_Abstract
{
    /**
     *
     */
    const CONFIG = "mybms/popup/is_already_displayed";

    /**
     * Can show the notification popup
     *
     * @return bool
     */
    public function canShow()
    {
        return (Mage::getStoreConfig(self::CONFIG) != 1);
    }

    /**
     * Set the notification popup as displayed
     *
     * @return bool
     */
    public function setAsDisplay()
    {
        $config = Mage::getModel('core/config');
        $config->saveConfig(self::CONFIG,1);
        $config->cleanCache();
    }
}