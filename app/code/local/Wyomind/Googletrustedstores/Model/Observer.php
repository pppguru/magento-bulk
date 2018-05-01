<?php

class Wyomind_Googletrustedstores_Model_Observer {

//    public function configsaved($website,$store,$section) {
//        if (Mage::getSingleton('adminhtml/config_data')->getSection() == 'googletrustedstores') {
//            $gts = new Googletrustedstores();
//        }
//    }


    public function generateFeeds($schedule) {

        if (Mage::getStoreConfig("googletrustedstores/schedule/gts_dynamic_link") == "0") {
            return;
        }

        $errors = array();
        $report = "*** " . Mage::getStoreConfig("googletrustedstores/schedule/report_title") . " ***" . "\n\n";
        $debug = "<pre>*** Google Trusted Stores - DEBUG ***" . "<br><br>";

        $gts = Mage::getModel('googletrustedstores/googletrustedstores');
		
        try {

            $log[] = "--> Running Google Truted Stores <--";

            $update = Mage::getStoreConfig("googletrustedstores/schedule/last_update");
            $cron['curent']['localDate'] = Mage::getSingleton('core/date')->date('l Y-m-d H:i:s');
            $cron['curent']['gmtDate'] = Mage::getSingleton('core/date')->gmtDate('l Y-m-d H:i:s');
            $cron['curent']['localTime'] = Mage::getSingleton('core/date')->timestamp();
            $cron['curent']['gmtTime'] = Mage::getSingleton('core/date')->gmtTimestamp();


            $cron['file']['localDate'] = Mage::getSingleton('core/date')->date('l Y-m-d H:i:s', $update);
            $cron['file']['gmtDate'] = Mage::getSingleton('core/date')->gmtDate('l Y-m-d H:i:s', $update);
            $cron['file']['localTime'] = Mage::getSingleton('core/date')->timestamp($update);
            $cron['file']['gmtTime'] = strtotime($update);

            /* Magento getGmtOffset() is bugged and doesn't include daylight saving time, the following workaround is used */
            // date_default_timezone_set(Mage::app()->getStore()->getConfig('general/locale/timezone'));
            // $date = new DateTime();
            //$cron['offset'] = $date->getOffset() / 3600;
            $cron['offset'] = Mage::getSingleton('core/date')->getGmtOffset("hours");



            $log[] = '   * Last update : ' . $cron['file']['gmtDate'] . " GMT / " . $cron['file']['localDate'] . ' GMT' . $cron['offset'];
            $log[] = '   * Current date : ' . $cron['curent']['gmtDate'] . " GMT / " . $cron['curent']['localDate'] . ' GMT' . $cron['offset'];


            $cronExpr = json_decode($gts->getCronExpr());


            $i = 0;
            $done = false;

            foreach ($cronExpr->days as $d) {

                foreach ($cronExpr->hours as $h) {
                    $time = explode(':', $h);
                    if (date('l', $cron['curent']['gmtTime']) == $d) {
                        $cron['tasks'][$i]['localTime'] = strtotime(Mage::getSingleton('core/date')->date('Y-m-d')) + ($time[0] * 60 * 60) + ($time[1] * 60);
                        $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                    } else {
                        $cron['tasks'][$i]['localTime'] = strtotime("last " . $d, $cron['curent']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                        $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                    }
                    if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['curent']['localTime'] && $done != true) {

                        $log[] = '   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']);

                        // for all websites
                        $websites = Mage::app()->getWebsites();
                        foreach ($websites as $website) {

                            if ($gts->generateShipmentsFeed($website->getId()) && $gts->generateCancellationsFeed($website->getId())) {
                                $done = true;
                                $cnt++;
                                $log[] = '   * EXECUTED!';
                                $gts->setLastUpdate();
                            }
                        }
                    }
                    $i++;
                }
            }
        } catch (Exception $e) {
            $log[] = '   * ERROR! ' . ($e->getMessage());
        }
        if (!$done)
            $log[] = '   * SKIPPED!';

        if (Mage::getStoreConfig("googletrustedstores/schedule/enable_report")) {
            foreach (explode(',', Mage::getStoreConfig("googletrustedstores/schedule/emails")) as $email) {
                try {
                    if ($cnt)
                        mail($email, Mage::getStoreConfig("googletrustedstores/schedule/report_title"), "\n" . implode($log, "\n"));
                } catch (Exception $e) {
                    $log[] = '   * EMAIL ERROR! ' . ($e->getMessage());
                }
            }
        };
        if (isset($_GET['gts']))
            echo "<br/>" . implode($log, "<br/>");
        Mage::log("\n" . implode($log, "\n"), null, "GoogleTrustedStores-cron.log");
    }

}
