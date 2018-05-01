<?php

/**
 * Class PopupController
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MDN_MyBms_Adminhtml_MyBms_PopupController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Ajax call to set the notification popup as displayed
     *
     * @return string
     */
    public function closeAjaxAction()
    {
        $status = 0;
        $message = '';

        try
        {
            Mage::helper('MyBms/Popup')->setAsDisplay();
        }
        catch (exception $ex)
        {
            $status = 1;
            $message = $ex->getMessage();
        }

        return json_encode(array('status' => $status, 'message' => $message));
    }
}