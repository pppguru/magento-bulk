<?php

/**
 * Class MDN_ProductReturn_Model_Message
 *
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_RmaMessage extends Mage_Core_Model_Abstract {

    const AUTHOR_ADMIN = 'admin';
    const AUTHOR_CUSTOMER = 'customer';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('ProductReturn/RmaMessage');
    }

    /**
     * @param MDN_ProductReturn_Model_Rma $rma
     * @param string $message
     * @param string $from
     * @throws \Exception
     */
    public function sendMessage($rma, $message, $from){

        $this->setrmam_rma_id($rma->getrma_id())
            ->setrmam_message($message)
            ->setrmam_author($from)
            ->save();

        $data = array(
            'message' => $message,
            'rma_reference' => $rma->getrma_ref(),
            'rma_customer_name' => $rma->getCustomer()->getName()
        );

        $templateId = Mage::getStoreConfig('productreturn/messages/'.$from.'_email_template', $rma->getCustomer()->getStoreId());

        if (!$templateId)
            throw new Exception('Template not configured for Product Return messages');

        $identityId = Mage::getStoreConfig('productreturn/messages/email_identity', $rma->getCustomer()->getStoreId());

        $destEmail = '';
        switch($from){
            case self::AUTHOR_CUSTOMER:
                $destEmail = Mage::getModel('admin/user')->load($rma->getrma_manager_id())->getemail();
                break;
            case self::AUTHOR_ADMIN:
                $destEmail = $rma->getCustomer()->getemail();
                break;
        }

        if(empty($destEmail))
            throw new Exception('Not able to find email');

        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'adminhtml', 'store' => $rma->getCustomer()->getStoreId()))
            ->sendTransactional(
                $templateId,
                $identityId,
                $destEmail,
                $rma->getCustomer()->getname(),
                $data,
                $rma->getSalesOrder()->getstore_id());

    }

    public function _beforeSave(){

        if(!$this->getId()){
            $this->setrmam_date(Mage::getSingleton('core/date')->timestamp());
        }

        return parent::_beforeSave();

    }

}