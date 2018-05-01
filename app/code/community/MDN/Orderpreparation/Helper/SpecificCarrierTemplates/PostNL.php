<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_PostNL extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract
{

    public function createExportFile($orderPreparationCollection)
    {
        $output = '';
        foreach($orderPreparationCollection as $orderToPrepare)
        {
            $shipment = mage::getModel('sales/order_shipment')->loadByIncrementId($orderToPrepare->getshipment_id());
            if ($shipment->getId()) {

                //Use http://www.magentocommerce.com/magento-connect/postnl-extension-for-magento.html
                if(Mage::helper('postnl/carrier')->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())){
                    $this->_processDownload($this->getPrintShippingLabelUrl($shipment->getId()));
                    break;
                }
            }
        }

        return $output;
    }



    protected function _processDownload($url) {
        header('Location: '.$url);
        die();
    }

    /**
     * Get adminhtml url for PostNL print shipping label action.
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getPrintShippingLabelUrl($shipmentId)
    {

		$url = Mage::helper('adminhtml')->getUrl('adminhtml/postnlAdminhtml_shipment/printLabel',array('shipment_id' => $shipmentId));
		return $url;
		
		//Old url before magento SUPEE patches
        //return Mage::helper('adminhtml')->getUrl('postnl_admin/adminhtml_shipment/printLabel',array('shipment_id' => $shipmentId));
    }

    //FOR LATER INTEGRATION :

    /**
     * Get adminhtml url for PostNL print return label action.
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getPrintReturnLabelUrl($shipmentId)
    {
        return Mage::helper('adminhtml')->getUrl('postnl_admin/adminhtml_shipment/printReturnLabel',array('shipment_id' => $shipmentId));
    }

    /**
     * Get adminhtml url for PostNL print packing slip action.
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getPrintPackingSlipUrl($shipmentId)
    {
        return Mage::helper('adminhtml')->getUrl('postnl_admin/adminhtml_shipment/printPackingSlip',array('shipment_id' => $shipmentId));
    }

    /**
     * Get adminhtml url for PostNL remove labels action
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getRemoveLabelsUrl($shipmentId)
    {
        return Mage::helper('adminhtml')->getUrl('postnl_admin/adminhtml_shipment/removeLabels',array('shipment_id' => $shipmentId));
    }


}