<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Lib_Model_AdminhtmlPdf_Sales_Order_Pdf_Creditmemo extends Mage_Sales_Model_Order_Pdf_Creditmemo
{
    // Column count in layout section
    const ONE_COLUMN_LAYOUT = 1;
    const TWO_COLUMN_LAYOUT = 2;

    protected $_pdfCreditmemoInterface = 'AW_Lib_Model_AdminhtmlPdf_Sales_Order_Pdf_CreditmemoInterface';

    protected $_sectionModels = array();

    /**
     * Collect Section models from 3rd modules
     *
     * @return AW_Lib_Model_AdminhtmlPdf_Sales_Order_Pdf_Creditmemo
     */
    public function collectSectionModels()
    {
        $pdfCreditmemoNode = Mage::getConfig()->getNode("aw_lib/models/pdf/creditmemo");
        if ($pdfCreditmemoNode) {
            $configData = $pdfCreditmemoNode->asArray();
            uasort($configData, array($this, '_compareConfig'));
            foreach ($configData as $moduleCode => $moduleConfig) {
                if (array_key_exists('model', $moduleConfig)) {
                    $modelInstance = Mage::getModel($moduleConfig['model']);
                    if (($modelInstance instanceof $this->_pdfCreditmemoInterface) && $modelInstance->canDraw()) {
                        $modelInstance->setOrderModel($this->getOrderModel());
                        $this->_sectionModels[$moduleCode] = $modelInstance;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Insert title and number for concrete document type
     *
     * @param  Zend_Pdf_Page $page
     * @param  string $text
     * @return void
     */
    public function insertDocumentNumber(Zend_Pdf_Page $page, $text)
    {
        // get first page for number
        $page = reset($this->_getPdf()->pages);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText($text, 35, $docHeader[1] - 15, 'UTF-8');
    }

    /**
     * Insert order to pdf page
     *
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $obj
     * @param bool $putOrderId
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        /* AW_Lib START */
        $this->setOrderModel($order);
        $this->collectSectionModels();
        /* AW_Lib END */

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates(array(25, $top, 570, $top - 55));
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->drawText(
                Mage::helper('sales')->__('Order # ') . $order->getRealOrderId(), 35, ($top -= 30), 'UTF-8'
            );
        }
        $page->drawText(
            Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate(
                $order->getCreatedAtStoreDate(), 'medium', false
            ),
            35,
            ($top -= 15),
            'UTF-8'
        );

        $top -= 10;
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, ($top - 25));
        $page->drawRectangle(275, $top, 570, ($top - 25));

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true)
            ->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
            $shippingMethod  = $order->getShippingDescription();
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(Mage::helper('sales')->__('Sold to:'), 35, ($top - 15), 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(Mage::helper('sales')->__('Ship to:'), 285, ($top - 15), 'UTF-8');
        } else {
            $page->drawText(Mage::helper('sales')->__('Payment Method:'), 285, ($top - 15), 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (!$order->getIsVirtual() && isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, ($top - 25), 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value){
                if ($value!=='') {
                    $text = array();
                    foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            /* AW_Lib after Address section START */
            foreach ($this->_sectionModels as $sectionModel) {
                if ($sectionModel->hasAfterAddressSection()) {
                    $this->insertSection($page, $this->y, $sectionModel->getAddressSectionData());
                }
            }
            /* AW_Lib after Address section END */

            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y-25);
            $page->drawRectangle(275, $this->y, 570, $this->y-25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('sales')->__('Payment Method'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Shipping Method:'), 285, $this->y , 'UTF-8');

            $this->y -=10;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments   = $this->y - 15;
        } else {
            $yPayments   = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value){
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if (!$order->getIsVirtual()) {
            $topMargin    = 15;
            $methodStartY = $this->y;
            $this->y     -= 15;

            foreach (Mage::helper('core/string')->str_split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " "
                . $order->formatPriceTxt($order->getShippingAmount()) . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = array();
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(Mage::helper('sales')->__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {

                    $CarrierCode = $track->getCarrierCode();
                    if ($CarrierCode != 'custom') {
                        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);
                        $carrierTitle = $carrier->getConfigData('title');
                    } else {
                        $carrierTitle = Mage::helper('sales')->__('Custom Value');
                    }

                    //$truncatedCarrierTitle = substr($carrierTitle, 0, 35) . (strlen($carrierTitle) > 35 ? '...' : '');
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    //$page->drawText($truncatedCarrierTitle, 285, $yShipments , 'UTF-8');
                    $page->drawText($truncatedTitle, 292, $yShipments , 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments , 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25,  $methodStartY, 25,  $currentY); //left
            $page->drawLine(25,  $currentY,     570, $currentY); //bottom
            $page->drawLine(570, $currentY,     570, $methodStartY); //right

            $this->y = $currentY - 7;

            /* AW_Lib after Payment/Shipment section START */
            foreach ($this->_sectionModels as $sectionModel) {
                if ($sectionModel->hasAfterPaymentShipmentSection()) {
                    $this->insertSection($page, $this->y, $sectionModel->getPaymentShipmentSectionData());
                }
            }
            /* AW_Lib after Payment/Shipment section END */
        }

        /* AW_Lib after Custom section START */
        foreach ($this->_sectionModels as $sectionModel) {
            if ($sectionModel->hasCustomSection()) {
                $this->insertSection($page, $this->y, $sectionModel->getCustomSectionData());
            }
        }
        /* AW_Lib after Custom section END */
    }

    /**
     * Insert section to PDF page
     *
     * @param Zend_Pdf_Page $page
     * @param int $yCoordinate
     * @param array $sectionData
     */
    public function insertSection(&$page, &$yCoordinate, $sectionData)
    {
        $sectionTitles = array(
            'left'  => null,
            'right' => null,
        );
        if (array_key_exists('left', $sectionData['column'])) {
            $sectionTitles['left'] = $sectionData['column']['left']['title'];
        }
        if (array_key_exists('right', $sectionData['column'])) {
            $sectionTitles['right'] = $sectionData['column']['right']['title'];
        }

        if (count(array_filter($sectionTitles))) {
            $this->_renderSectionHeader($page, $yCoordinate, $sectionTitles, $sectionData['layout_type']);
        }

        $this->_renderSectionBody($page, $yCoordinate, $sectionData['column'], $sectionData['layout_type']);
    }

    /**
     * Render section header
     *
     * @param Zend_Pdf_Page $page
     * @param int $yCoordinate
     * @param string $sectionTitle
     * @param int $layoutType
     */
    protected function _renderSectionHeader(&$page, &$yCoordinate, $sectionTitle, $layoutType = self::ONE_COLUMN_LAYOUT)
    {
        // If page is ends than add new page
        if ($yCoordinate < 80) {
            $page = $this->newPage();
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $this->_setFontRegular($page, 10);
        }

        // Draw header rectangle
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $yCoordinate, 570, $yCoordinate - 25);

        // Draw delimiter line
        if ($layoutType === self::TWO_COLUMN_LAYOUT) {
            $page->drawLine(275,  $yCoordinate, 275,  $yCoordinate - 25);
        }

        // Draw left section titles
        $yCoordinate -= 15;
        if (!is_null($sectionTitle['left'])) {
            $this->_setFontBold($page, 12);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText($sectionTitle['left'], 35, $yCoordinate, 'UTF-8');
            $this->_setFontRegular($page, 10);
        }

        // Draw right section titles
        if (!is_null($sectionTitle['right'])) {
            $this->_setFontBold($page, 12);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText($sectionTitle['right'], 285, $yCoordinate, 'UTF-8');
            $this->_setFontRegular($page, 10);
        }

        $yCoordinate -= 17;
    }

    /**
     * Render section header
     *
     * @param Zend_Pdf_Page $page
     * @param int $yCoordinate
     * @param array $sectionValues
     * @param int $layoutType
     *
     * @return Zend_Pdf_Page
     */
    protected function _renderSectionBody(&$page, &$yCoordinate, array $sectionValues, $layoutType = self::ONE_COLUMN_LAYOUT)
    {
        $preparedSectionValues = $this->_prepareSectionValues($sectionValues, $layoutType);
        $lineCount = max(count($preparedSectionValues['left']), count($preparedSectionValues['right']));
        if ($lineCount > 0) {
            $sectionStartY = $yCoordinate + 7;
            for ($i = 0; $i < $lineCount; $i++) {
                if ($yCoordinate < 45) {
                    $yCoordinate -= 15;
                    $page->drawLine(25,  $sectionStartY, 570, $sectionStartY); // top
                    $page->drawLine(25,  $sectionStartY, 25,  $yCoordinate);   // left
                    $page->drawLine(25,  $yCoordinate,   570, $yCoordinate);   // bottom
                    $page->drawLine(570, $yCoordinate,   570, $sectionStartY); // right

                    $page = $this->newPage();
                    $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
                    $page->setLineWidth(0.5);
                    $this->_setFontRegular($page, 10);
                    $sectionStartY = 800;
                    $this->y -= 10;
                }
                if (isset($preparedSectionValues['left'][$i])) {
                    $this->_drawPreparedValue($page, $yCoordinate, 35, $preparedSectionValues['left'][$i]);
                }
                if (isset($preparedSectionValues['right'][$i])) {
                    $this->_drawPreparedValue($page, $yCoordinate, 285, $preparedSectionValues['right'][$i]);
                }
                $yCoordinate -= 10;
            }

            $yCoordinate -= 15;
            $page->drawLine(25,  $sectionStartY, 570, $sectionStartY); // top
            $page->drawLine(25,  $sectionStartY, 25,  $yCoordinate);   // left
            $page->drawLine(25,  $yCoordinate,   570, $yCoordinate);   // bottom
            $page->drawLine(570, $yCoordinate,   570, $sectionStartY); // right
            $yCoordinate -= 7;
            if ($yCoordinate < 45) {
                $page = $this->newPage();
                $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
                $page->setLineWidth(0.5);
                $this->_setFontRegular($page, 10);
            }
        }
        return $page;
    }

    /**
     * Draw prepared value to PDF page
     *
     * @param Zend_Pdf_Page $page
     * @param int $yCoordinate
     * @param int $xCoordinate
     * @param array $preparedValue
     *
     * @return Zend_Pdf_Page
     */
    protected function _drawPreparedValue(&$page, &$yCoordinate, $xCoordinate, $preparedValue)
    {
        switch ($preparedValue['type']) {
            case 'value':
                $this->_setFontRegular($page, 10);
                $page->drawText($preparedValue['value'], $xCoordinate + 15, $yCoordinate - 10, 'UTF-8');
                break;
            case 'label':
                $this->_setFontBold($page, 10);
                $page->drawText($preparedValue['value'], $xCoordinate, $yCoordinate - 10, 'UTF-8');
                break;
        }
        return $page;
    }

    /**
     * Collect lines
     *
     * @param array $sectionValues
     * @param int $layoutType
     *
     * @return array
     */
    protected function _prepareSectionValues(array $sectionValues, $layoutType = self::ONE_COLUMN_LAYOUT)
    {
        $preparedValues = array(
            'left'  => array(),
            'right' => array(),
        );

        foreach ($sectionValues as $position => $valuesArray) {
            foreach ($valuesArray['values'] as $value) {
                $preparedValues[$position] = array_merge($preparedValues[$position], $this->_explodeToLines($value, $layoutType));
            }
        }

        return $preparedValues;
    }

    /**
     * Prepare section values data
     * - Explode to lines by length
     *
     * @param string $value
     * @param int $layoutType
     *
     * @return array
     */
    protected function _explodeToLines($value, $layoutType = self::ONE_COLUMN_LAYOUT)
    {
        $valueLines = array();
        if ($layoutType == self::ONE_COLUMN_LAYOUT) {
            $lineLength = 85;
        } else {
            $lineLength = 50;
        }

        $value['value'] = preg_replace('/<br[^>]*>/i', "\n", $value['value']);
        $value['value'] = explode("\n", $value['value']);
        foreach ($value['value'] as $valueLine) {
            foreach (Mage::helper('core/string')->str_split($valueLine, $lineLength, true, true) as $preparedValueLine) {
                $valueLines[] = array(
                    'value' => $preparedValueLine,
                    'type'  => $value['type'],
                );
            }
        }

        return $valueLines;
    }

    /**
     * Calculate address section height
     *
     * @param  array $address
     * @return int Height
     */
    protected function _calcAddressHeight($address)
    {
        $y = 0;
        foreach ($address as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 55, true, true) as $_value) {
                    $text[] = $_value;
                }
                $y += count($text) * 15;
            }
        }
        return $y;
    }

    /**
     * uasort callback
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    static function _compareConfig($a, $b)
    {
        $aSortOrder = (int)$a['sort_order'];
        $bSortOrder = (int)$b['sort_order'];
        if ($aSortOrder === $bSortOrder) {
            return 0;
        }
        return ($aSortOrder > $bSortOrder) ? +1 : -1;
    }
}