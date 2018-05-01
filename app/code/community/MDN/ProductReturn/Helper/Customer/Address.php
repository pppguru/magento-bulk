<?php

/**
 * Class MDN_ProductReturn_Helper_Customer_Address
 *
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Helper_Customer_Address extends Mage_Core_Helper_Abstract {

    /**
     * @var null|array
     */
    protected $addresses = null;

    /**
     * @param $productReturn
     * @return array|null
     */
    protected function getAddressesList($productReturn){

        if(is_null($this->addresses)){

            $this->addresses = array(
                'customer' => array(),
                'order' => array()
            );

            foreach ($productReturn->getCustomer()->getAddresses() as $address) {

                $this->addresses['customer'][] = $address;

            }

            $orderShippingAddress = $productReturn->getSalesOrder()->getShippingAddress();

            if($orderShippingAddress != null && $orderShippingAddress->getId()>0){

                $this->addresses['order'][] = $orderShippingAddress;

            }

        }

        return $this->addresses;

    }

    /**
     * @param $productReturn
     * @param string $name
     * @param string $value
     * @return string $html
     */
    public function getAddressesAsCombo($productReturn, $name, $value)
    {
        $html   = '<select name="' . $name . '" id="' . $name . '">';

        $addresses = $this->getAddressesList($productReturn);

        foreach($addresses['customer'] as $customerShippingAddress){

            $selected = ($value == $customerShippingAddress->getId()) ? ' selected="selected" ' : '';
            $html .= '<option value="' . $customerShippingAddress->getId() . '" ' . $selected . '>' . $customerShippingAddress->getFormated() . '</option>';

        }

        if(isset($addresses['order'][0]) && is_object($addresses['order'][0])) {
            $orderShippingAddress = $addresses['order'][0];
            //value="0" because it enable to distinct Customer shipping address from order shipping address
            $selected = ($value == 0 || $value == null || $value == $orderShippingAddress->getId()) ? ' selected="selected" ' : '';
            $html .= '<option value="0" ' . $selected . '>' . $orderShippingAddress->getFormated() . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * @param $productReturn
     * @param string $value
     * @return string $html
     */
    public function getAddressesAsTxt($productReturn, $value)
    {
        $html = '';

        $addresses = $this->getAddressesList($productReturn);
        foreach($addresses['customer'] as $customerShippingAddress){

            if($value == $customerShippingAddress->getId()) {
                $html = $this->formatAddressForHtmlDisplay($customerShippingAddress);
                break;
            }

        }

        if(isset($addresses['order'][0]) && is_object($addresses['order'][0])) {

            $orderShippingAddress = $addresses['order'][0];
            if ($value == 0 || $value == $orderShippingAddress->getId()){
                $html = $this->formatAddressForHtmlDisplay($orderShippingAddress);
            }

        }

        return $html;
    }

    /**
     * @param $address
     * @param string $caption
     * @param bool $show_details
     * @param string $NoTvaIntraco
     * @return string
     */
    protected function formatAddressForHtmlDisplay($address, $caption = '', $show_details = false, $NoTvaIntraco = '')
    {
        if ($NoTvaIntraco == 'taxvat')
            $NoTvaIntraco = '';
        $FormatedAddress = "";
        if ($caption != '')
            $FormatedAddress = $caption . "<br/> ";
        if ($address != null) {
            if ($address->getcompany() != '')
                $FormatedAddress .= $address->getcompany() . "<br/> ";
            if ($address->getPrefix() == '')
                $FormatedAddress .= 'M. ';
            $FormatedAddress .= $address->getName() . "<br/> ";
            $FormatedAddress .= $address->getStreet(1) . "<br/> ";
            if ($address->getStreet(2) != '')
                $FormatedAddress .= $address->getStreet(2) . "<br/> ";
            if ($show_details) {
                if ($address->getbuilding() != '')
                    $FormatedAddress .= ' Bat ' . $address->getbuilding();
                if ($address->getfloor() != '')
                    $FormatedAddress .= ' Etage ' . $address->getfloor();
                if ($address->getdoor_code() != '')
                    $FormatedAddress .= ' Code ' . $address->getdoor_code();
                if ($address->getappartment() != '')
                    $FormatedAddress .= ' Appt ' . $address->getappartment();
                $FormatedAddress .= "<br/> ";
            }
            $FormatedAddress .= $address->getPostcode() . ' ' . $address->getCity() . "<br/> ";
            $FormatedAddress .= strtoupper(Mage::getModel('directory/country')->load($address->getCountry())->getName()) . "<br/> ";
            if ($show_details)
                $FormatedAddress .= $address->getcomments() . "<br/> ";
            if ($NoTvaIntraco != '')
                $FormatedAddress .= 'No TVA : ' . $NoTvaIntraco;
        }

        return $FormatedAddress;
    }

}