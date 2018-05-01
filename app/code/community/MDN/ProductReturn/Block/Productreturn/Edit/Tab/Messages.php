<?php

/**
 * Class MDN_ProductReturn_Block_ProductReturn_Edit_Tab_Messages
 *
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Block_Productreturn_Edit_Tab_Messages extends Mage_Adminhtml_Block_Widget_Form {

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ProductReturn/Tab/Messages.phtml');
    }

    public function getMessages(){

        return Mage::registry('current_rma')->getMessages();

    }

}