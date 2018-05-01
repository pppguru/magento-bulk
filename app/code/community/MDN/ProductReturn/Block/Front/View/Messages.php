<?php

/**
 * Class MDN_ProductReturn_Block_Front_View_Messages
 *
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Block_Front_View_Messages extends Mage_Core_Block_Template {

    /**
     * @var MDN_ProductReturn_Model_Rma
     */
    private $_productReturn;

    /**
     * @return \Mage_Core_Model_Abstract|\MDN_ProductReturn_Model_Rma
     * @throws \Exception
     */
    public function getRma()
    {
        if ($this->_productReturn == null) {
            $productReturnId      = $this->getRequest()->getParam('rma_id');
            $this->_productReturn = mage::getModel('ProductReturn/Rma')->load($productReturnId);
        }

        return $this->_productReturn;
    }

    public function getSubmitUrl(){

        return $this->getUrl('ProductReturn/Front/SubmitMessage');

    }

}