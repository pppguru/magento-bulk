<?php

class Wyomind_Googletrustedstores_Adminhtml_GoogletrustedstoresController extends Mage_Adminhtml_Controller_Action {

    protected function _sendUploadResponse($fileName, $content, $contentType = "application/octet-stream") {
        $response = $this->getResponse();
        $response->setHeader("HTTP/1.1 200 OK", "");
        $response->setHeader("Pragma", "public", true);
        $response->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true);
        $response->setHeader("Content-Disposition", "attachment; filename=" . $fileName);
        $response->setHeader("Last-Modified", date("r"));
        $response->setHeader("Accept-Ranges", "bytes");
        $response->setHeader("Content-Length", strlen($content));
        $response->setHeader("Content-type", $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    public function previewshipmentsAction() {
        $website = $this->getRequest()->getParam('website');
        $model = Mage::getModel('googletrustedstores/googletrustedstores');
        $csv = $model->generateShipmentsFeed($website,true);
        if ($model->_demo) {
            $this->_getSession()->addError(Mage::helper('googletrustedstores')->__("Invalid license."));
            Mage::getConfig()->saveConfig('googletrustedstores/license/activation_code', '', 'default', '0');
            Mage::getConfig()->cleanCache();
             $this->_redirect('adminhtml/system_config/edit/section/googletrustedstores/');
        } else if ($this->getRequest()->getParam('dl')) {
            $this->_sendUploadResponse(Mage::getStoreConfig("googletrustedstores/shipments_settings/filename"), $csv);
        } else {
            echo "<pre>" . $csv . "</pre>";
        }
    }

    public function previewcancellationsAction() {
        $website = $this->getRequest()->getParam('website');
        $model = Mage::getModel('googletrustedstores/googletrustedstores');
        $csv = $model->generateCancellationsFeed($website,true);
        if ($this->getRequest()->getParam('dl')) {
            $this->_sendUploadResponse(Mage::getStoreConfig("googletrustedstores/cancellations_settings/filename"), $csv);
        } else {
            echo "<pre>" . $csv . "</pre>";
        }
    }

}
