<?php

/**
 * Class Notifications
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class MDN_MyBms_Helper_Notifications extends Mage_Core_Helper_Abstract
{
    /**
     * Parsing Notifications Json and add notice.
     * If DB does not have the notice we add it.
     */
    public function getNotificationsList()
    {
        $notificationListHasBeenDownloaded = false;

        $dataArray = Mage::Helper("MyBms/Webservice")->getNotificationsList();

        if($this->isValidArray($dataArray)) {
            $notificationListHasBeenDownloaded = true;
            $model = Mage::getModel('AdminNotification/Inbox');
            foreach ($dataArray as $notification) {
                if(array_key_exists('bmn_name',$notification) && array_key_exists('bmn_subject',$notification) && strlen($notification['bmn_name'])>0) {
                    $collection = $model->getCollection()->addFieldToFilter('title', $notification['bmn_name']);
                    $toAdd = true;
                    if($collection->getSize()>0) {
                        $notificationTitle = $collection->getFirstItem();
                        if ($notificationTitle->description == $notification['bmn_subject']) {
                            $toAdd = false;
                        }
                    }
                    if($toAdd){
                        $model->addNotice($notification['bmn_name'], $notification['bmn_subject'], $notification['bmn_url']);
                    }
                }
            }
        }
        return $notificationListHasBeenDownloaded;
    }

    function isValidArray($dataArray){
        return ($dataArray != null
            && is_array($dataArray)
            && count($dataArray) > 0) ? true : false;
    }
}