<?php

class MDN_CompetitorPrice_Adminhtml_CompetitorPrice_ApiController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $action = $this->getRequest()->getPost('action');

        $body = array();

        try
        {
            switch($action)
            {
                case 'add_to_monitoring':
                    $productId = $this->getRequest()->getPost('product_id');
                    $productData = json_decode($this->getRequest()->getPost('product_data'), true);
                    $channel = $productData['channel'];
                    $this->addProductToMonitoring($productId, $channel);
                    $productData['product_id'] = $productId;
                    $body = json_encode($productData);
                    break;

                default:
                    $products = array_filter(json_decode($_POST['products'], true), function($product) { return (!empty($product['ean']) || !empty($product['reference'])); });
                    $offers = Mage::getSingleton('CompetitorPrice/Offers')->getOffers($products);
                    $body = json_encode($offers['body']);
                    break;
            }
        }
        catch(Exception $ex)
        {
            Mage::helper('CompetitorPrice')->log($ex->getMessage()."\n".$ex->getTraceAsString());
            $body = json_encode(array('errors' => array($ex->getMessage())));
        }


        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($body);
    }


    protected function _isAllowed()
    {
        return true;
    }


    protected function addProductToMonitoring($productId, $channel)
    {
        Mage::getModel('CompetitorPrice/Product')->add($productId, $channel);
    }
}
