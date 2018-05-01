<?php

class MDN_ProductReturn_Helper_Returnlabel extends Mage_Core_Helper_Abstract
{

    public function getReturnlabelUrl($rma)
    {
        $file = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'rma_return_labels/' . md5($rma->getrma_id() . '-' . $rma->getrma_ref()) . '.pdf';

        return $file;
    }


    public function isExists($rma)
    {
        $file = $this->getBaseReturnlabelUrl($rma);

        return file_exists($file);
    }

    public function getBaseReturnlabelUrl($rma)
    {
        return Mage::getBaseDir('media') . DS . 'rma_return_labels' . DS . md5($rma->getrma_id() . '-' . $rma->getrma_ref()) . '.pdf';
    }

    public function deleteLabel($rma)
    {
        $path = $this->getBaseReturnlabelUrl($rma);
        unlink($path);
    }

    public function notifyLabel($rma)
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $templateId = Mage::getStoreConfig('productreturn/emails/template_return_label', $rma->getCustomer()->getStoreId());
        if (!$templateId)
            throw new Exception('Template not configured for shipping label email');
        $identityId = Mage::getStoreConfig('productreturn/emails/email_identity', $rma->getCustomer()->getStoreId());

        $customerEmail = $rma->getCustomer()->getemail();
        if ($rma->getrma_customer_email() != '')
            $customerEmail = $rma->getrma_customer_email();

        //set data to use in array
        $data = array
        (
            'caption'                => $rma->getcaption(),
            'customer_name'          => $rma->getCustomer()->getName(),
            'rma_id'                 => $rma->getrma_ref(),
            'order_id'               => $rma->getSalesOrder()->getincrement_id(),
            'rma_expire_date'        => mage::helper('core')->formatDate($rma->getrma_expire_date(), 'short'),
            'store_name'             => $rma->getSalesOrder()->getStoreGroupName(),
            'rma_reason'             => mage::helper('ProductReturn')->__($rma->getrma_reason()),
            'rma_status'             => mage::helper('ProductReturn')->__($rma->getrma_status()),
            'rma_description'        => $rma->getrma_public_description(),
            'rma_public_description' => $rma->getrma_public_description(),
            'rma_action'             => mage::helper('ProductReturn')->__($rma->getrma_action()),
            'product_html'           => $rma->getProductsAsHtml()
        );

        //send email
        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'adminhtml', 'store' => $rma->getCustomer()->getStoreId()))
            ->sendTransactional(
                $templateId,
                $identityId,
                $customerEmail,
                $rma->getCustomer()->getname(),
                $data,
                $rma->getSalesOrder()->getstore_id());

        //send email to cc_to
        $cc = mage::getStoreConfig('productreturn/emails/cc_to');
        if ($cc != '') {
            Mage::getModel('core/email_template')
                ->setDesignConfig(array('area' => 'adminhtml', 'store' => $rma->getCustomer()->getStoreId()))
                ->sendTransactional(
                    $templateId,
                    $identityId,
                    $cc,
                    $rma->getCustomer()->getname(),
                    $data,
                    $rma->getSalesOrder()->getstore_id());
        }

        $rma->addHistoryRma(mage::helper('ProductReturn')->__('Customer notified for shipping label'));

        $translate->setTranslateInline(true);

        return $this;
    }

}