<?php

class MDN_Orderpreparation_Model_Pdf_CustomLabel extends MDN_Orderpreparation_Model_Pdf_Pdfhelper {

    /**
     * @param array $shipmentIds
     * @return Zend_Pdf
     */
    public function getPdf($shipmentIds = array()) {


        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $this->pdf = new Zend_Pdf();

        $shipments = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('increment_id', array('in' => $shipmentIds));

        foreach ($shipments as $shipment) {
            $this->appendLabel($shipment);
        }

        $this->_afterGetPdf();

        return $this->pdf;
    }

    /**
     * @param $shipment
     */
    protected function appendLabel($shipment)
    {
        $settings = array();
        $settings['store_id'] = $shipment->getStoreId();
        $page = $this->NewPage($settings);

        $fontSize = Mage::getStoreConfig('orderpreparation/custom_label/size');
        $this->defineFont($page, $fontSize, self::FONT_MODE_BOLD);

        $marginTop = $this->mmToPoint(Mage::getStoreConfig('orderpreparation/custom_label/margin_top'));
        $marginLeft = $this->mmToPoint(Mage::getStoreConfig('orderpreparation/custom_label/margin_left'));

        $this->y -= $marginTop;

        $address = $this->buildAddressString($shipment);
        $this->DrawMultilineTextForLabel($page, $address, $marginLeft, $this->y, $fontSize, 0, $fontSize * 1.2);
    }

    public function NewPage(array $settings = null) {

        if ($settings)
            $this->_settings = $settings;

        $height = $this->mmToPoint(Mage::getStoreConfig('orderpreparation/custom_label/height'));
        $width = $this->mmToPoint(Mage::getStoreConfig('orderpreparation/custom_label/width'));

        $page = $this->pdf->newPage($width.':'.$height);
        $this->pdf->pages[] = $page;

        $this->y = $height;

        return $page;
    }

    /**
     * Convert millimeters to point
     *
     * @param $mm
     * @return mixed
     */
    protected function mmToPoint($mm)
    {
        return $mm * 2.84;
    }

    protected function buildAddressString($shipment)
    {
        $address = $shipment->getOrder()->getShippingAddress();
        $data = array_merge($address->getData(), $shipment->getData());
        foreach($address->getStreet() as $idx => $value)
        {
            $data['street'.($idx + 1)] = $value;
        }
        $data['country'] = Mage::getModel('directory/country')->loadByCode($data['country_id'])->getName();


        $this->checkDebugMode($data);

        $lines = Mage::getStoreConfig('orderpreparation/custom_label/lines');

        $pattern = "/(\{([^\}]*)\})/";
        $matches = array();
        preg_match_all($pattern, $lines, $matches);
        foreach($matches[2] as $key)
        {
            $replaceData = (array_key_exists($key,$data))?$data[$key]:'';
            $lines = str_replace('{' . $key . '}',$replaceData, $lines);
        }


        return $lines;
    }

    protected function checkDebugMode($data)
    {
        if (Mage::getStoreConfig('orderpreparation/custom_label/debug_mode'))
        {
            $html = '<table border="1" cellspacing="0">';
            $html .= "<tr><th>Code</th><th>Value</th>";
            foreach($data as $k => $v)
            {
                $html .= "<tr><td>".$k."</td><td>".$v."</td>";
            }
            $html .= '</table>';

            die($html);
        }
    }

     protected function DrawMultilineTextForLabel(&$page, $Text, $x, $y, $size, $GrayScale, $LineHeight, $allowNewPage = true) {
        $retour = -$LineHeight;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($GrayScale));
        $this->defineFont($page,$size);
        foreach (explode("\n", $Text) as $value) {
            if ($value !== '') {
                $page->drawText(trim(strip_tags($value)), $x, $y, 'UTF-8');
                $y -=$LineHeight;
                $retour += $LineHeight;
            }
        }
        return $retour;
    }
}

