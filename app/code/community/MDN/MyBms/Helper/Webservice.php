<?php
/**
 * Class Webservice
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

class MDN_MyBms_Helper_Webservice extends Mage_Core_Helper_Abstract
{
    CONST TIMEOUT_IN_SECOND = 15;
    /**
     * curl json parsing method
     *
     * @param $url
     * @return Json
     */
    public function curlList($url)
    {
        $result = '';

        if($url && (filter_var($url, FILTER_VALIDATE_URL) !== FALSE)) {
            try {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT_IN_SECOND);

                $result = curl_exec($ch);

                curl_close($ch);

            } catch (Exception $ex) {
                mage::logException($ex);
            }
        }

        return $result;
    }

    /**@TODO changer url
     *
     * LastRealease Json parsing.
     *
     * @return json decode
     */
    public function listBmsExtensions()
    {
        return json_decode($this->curlList(Mage::getConfig()->getNode('default/MyBms/webservice')->listbmsextensions));
    }

    /**
     *@TODO changer url
     *
     * Documentation Json parsing.
     *
     * @return json
     */
    public function listBmsDocumentation()
    {
        return $this->curlList(Mage::getConfig()->getNode('default/MyBms/webservice')->listbmsdocumentation);
    }

    /**
     * @TODO changer url
     *
     * Notification Json parsing.
     *
     * @return json decode
     */
    public function getNotificationsList()
    {
        return json_decode($this->curlList(Mage::getConfig()->getNode('default/MyBms/webservice')->getnotificationslist), true);
    }
}