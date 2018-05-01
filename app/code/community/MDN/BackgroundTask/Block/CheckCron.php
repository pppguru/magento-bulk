<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_BackgroundTask_Block_CheckCron extends Mage_Adminhtml_Block_Widget_Form {

    protected function _toHtml() {
        $html = '';

        if (Mage::getStoreConfig('healthyerp/erp/enabled') == 1){

            if (Mage::getStoreConfig('healthyerp/erp/disable_cron') == 0) {

                $html = '<div class="notification-global"> ';

                $message = 'Caution !! It seems that cron is not working on your server, ERP requires cron to work properly.';
                $messageHtml = '<font color=red><b>' . $message . '</b></font>';

                //get the latest cron execution date
                $sql = "select max(executed_at) from " . Mage::getConfig()->getTablePrefix() . "cron_schedule";
                $lastExecutionTime = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);

                //if return empty, check if there are records in table cron_schedule
                if ($lastExecutionTime == '') {
                    $sql = "select count(*) from " . Mage::getConfig()->getTablePrefix() . "cron_schedule";
                    $count = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);

                    if ($count == 0) {
                        //if no records in cron_schedule table, check if there are background tasks in table backgroundtask
                        $sql = "select count(*) from " . Mage::getConfig()->getTablePrefix() . "backgroundtask";
                        $count = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);

                        if ($count == 0) {
                            $html .= $messageHtml . '</div>';
                            return $html;
                        }
                    }
                }

                $timeStamp = strtotime($lastExecutionTime);
                if ((time() - $timeStamp) > 60 * 5) {
                    $html .= $messageHtml;
                } else
                    return '';

                $html .= '</div>';
            }
        }else{
            $message = 'Caution !! ERP is disabled - To activate it : System > configuration > ERP Info > ERP Magento integration > Enable ERP';
            $html = '<div class="notification-global"><font color=red><b>' . $message . '</b></font></div>';
        }
        
        return $html;
    }

}