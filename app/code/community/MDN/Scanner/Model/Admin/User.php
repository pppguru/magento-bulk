<?php

class MDN_Scanner_Model_Admin_User extends Mage_Admin_Model_User
{


    /**
     * Find admin start page url
     *
     * @return string
     */
    public function getStartupPageUrl()
    {
        if (!mage::helper('Scanner')->isMobileUserAgent())
        {
            return parent::getStartupPageUrl();
        }
        else
        {
            //redirect to scanner main screen
            return 'adminhtml/Scanner_index/index';
        }
        
    }


}
