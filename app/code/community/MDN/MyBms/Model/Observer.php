<?php

/**
 * Class Observer
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class MDN_MyBms_Model_Observer
{

    const XML_FREQUENCY_PATH    = 'MyBms/documentation/frequency';
    const XML_LAST_UPDATE_PATH  = 'MyBms/documentation/last_update';

    /**
     * Get notification list and reset timer
     */
    public function controllerActionPredispatch()
    {
        if(Mage::getStoreConfig('MyBms/notifications/enable')) {
            $currentTimeStamp = Mage::getModel('core/date')->timestamp();
            if (($this->getFrequency() + $this->getLastUpdate()) < $currentTimeStamp) {
                if(Mage::helper("MyBms/Notifications")->getNotificationsList()) {
                    $this->setLastUpdate($currentTimeStamp);
                }
            }
        }
    }

    /**
     * Frequence 1 day
     *
     * @return int
     */
    public function getFrequency()
    {
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH);
    }

    /**
     * last time a notification was added
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return Mage::getStoreConfig(self::XML_LAST_UPDATE_PATH);
    }

    /**
     * Save the last time a notification was added
     */
    public function setLastUpdate($currentTimeStamp)
    {
        $config = Mage::getModel('core/config');
        $config->saveConfig(self::XML_LAST_UPDATE_PATH,$currentTimeStamp);
        $config->cleanCache();
    }
}