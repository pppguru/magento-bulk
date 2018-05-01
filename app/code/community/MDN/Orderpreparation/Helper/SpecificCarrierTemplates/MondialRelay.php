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
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_MondialRelay extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract
{
	/**
	 * Generate XML output
	 *
	 * @param unknown_type $orderPreparationCollection
	 */
	public function createExportFile($orderPreparationCollection)
	{
		//merge PDF
		$shipmentsIds = array();
		foreach($orderPreparationCollection as $orderToPrepare)
		{
			$shipment = mage::getModel('sales/order_shipment')->loadByIncrementId($orderToPrepare->getshipment_id());
			if ($shipment->getId())
				$shipmentsIds[] = $shipment->getId();
		}
		
		//build pdf
        $urlEtiquette = mage::helper('pointsrelais/Label')->getPdfLabelUrlForShipment($shipmentsIds);
		$this->_processDownload($urlEtiquette, 'url');
		die('');
	}
	
    protected function _processDownload($resource, $resourceType) {

        $helper = Mage::helper('downloadable/download');
        $helper->setResource($resource, $resourceType);
        $fileName = $helper->getFilename();
        $contentType = $helper->getContentType();

        mage::app()->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', $contentType, true);

        if ($fileSize = $helper->getFilesize()) {
            mage::app()->getResponse()
                    ->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            mage::app()->getResponse()
                    ->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileName);
        }

        mage::app()->getResponse()
                ->clearBody();
        mage::app()->getResponse()
                ->sendHeaders();

        $helper->output();
    }
}