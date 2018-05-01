<?php
// require_once Mage::getModuleDir('controllers', 'Mage_Customer').DS.'AccountController.php';
include_once 'Mage/Customer/controllers/AccountController.php';

class Wholesale_Customer_AccountController extends Mage_Customer_AccountController {
    /**
     * Success Registration
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_AccountController
     */
    protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer, $wholesale=false)
    {
        $session = $this->_getSession();
        if ($customer->isConfirmationRequired()) {
            /** @var $app Mage_Core_Model_App */
            $app = $this->_getApp();
            /** @var $store  Mage_Core_Model_Store*/
            $store = $app->getStore();
            $customer->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );
            $customerHelper = $this->_getHelper('customer');
            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
            $url = $this->_getUrl('*/*/index', array('_secure' => true));
        } else {
            $session->setCustomerAsLoggedIn($customer);
            $session->renewSession();
            $url = $this->_welcomeCustomer($customer, false, $wholesale);
        }
        $this->_redirectSuccess($url);
        return $this;
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false, $wholesale = false)
    {
        if ($wholesale == true) {
            $this->_getSession()->addSuccess(
                $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
            );
            $this->_getSession()->addSuccess(
                $this->__('Your wholesale account will be activated by administrator.')
            );
        } else {
            $this->_getSession()->addSuccess(
                $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
            );
        }
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType =  $this->_getHelper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            Mage::app()->getStore()->getId()
        );

        $successUrl = $this->_getUrl('*/*/index', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }


    /**
     * Gets customer address
     *
     * @param $customer
     * @return array $errors
     */
    protected function _getErrorsOnCustomerAddressNew($customer)
    {
        $errors = array();
        /* @var $address Mage_Customer_Model_Address */
        $address = $this->_getModel('customer/address');
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = $this->_getModel('customer/form');
        $addressForm->setFormCode('customer_register_address')
            ->setEntity($address);

        $addressData = $addressForm->extractData($this->getRequest(), 'address', false);
        $addressErrors = $addressForm->validateData($addressData);
        if (is_array($addressErrors)) {
            $errors = array_merge($errors, $addressErrors);
        }
        $address->setId(null)
            ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
            ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
        $addressForm->compactData($addressData);
        $customer->addAddress($address);

        $addressErrors = $address->validate();
        if (is_array($addressErrors)) {
            $errors = array_merge($errors, $addressErrors);
        }

        if ($this->getRequest()->getPost('create_shipping_address')) {
            $shippingAddress = Mage::getModel('customer/address');
            $shippingAddressForm = Mage::getModel('customer/form');
            $shippingAddressForm->setFormCode('customer_register_address')
                ->setEntity($shippingAddress);

            $shippingAddressData = array(
                'firstname'  => $this->getRequest()->getPost('shipping_firstname'),
                'lastname'   => $this->getRequest()->getPost('shipping_lastname'),
                'company'    => $this->getRequest()->getPost('shipping_company'),
                'street'     => $this->getRequest()->getPost('shipping_street'),
                'city'       => $this->getRequest()->getPost('shipping_city'),
                'country_id' => $this->getRequest()->getPost('shipping_country_id'),
                'region'     => $this->getRequest()->getPost('shipping_region'),
                'region_id'  => $this->getRequest()->getPost('shipping_region_id'),
                'postcode'   => $this->getRequest()->getPost('shipping_postcode'),
                'website'  => $this->getRequest()->getPost('shipping_website'),
                'sell_online'  => $this->getRequest()->getPost('shipping_sell_online'),
                );

            // $shippingAddressErrors = $shippingAddressForm->validateData($shippingAddressData);
            $shippingAddressErrors = true;

            if ($shippingAddressErrors === true) {
                $shippingAddress->setId(null)
                    ->setIsDefaultBilling($this->getRequest()->getParam('shipping_default_billing', false))
                    ->setIsDefaultShipping($this->getRequest()->getParam('shipping_default_shipping', false));

                $shippingAddressForm->compactData($shippingAddressData);

                $customer->addAddress($shippingAddress);

                // $shippingAddressErrors = $shippingAddress->validate();

                if (is_array($shippingAddressErrors)) {
                    $errors = array_merge($errors, $shippingAddressErrors);
                }
            } else {
                $errors = array_merge($errors, $shippingAddressErrors);
            }}

        return $errors;
    }

    /**
     * Validate customer data and return errors if they are
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return array|string
     */
    protected function _getCustomerErrorsNew($customer)
    {
        $errors = array();
        $request = $this->getRequest();
        if ($request->getPost('create_address')) {
            $errors = $this->_getErrorsOnCustomerAddressNew($customer);
        }
        $customerForm = $this->_getCustomerForm($customer);
        $customerData = $customerForm->extractData($request);
        $customerErrors = $customerForm->validateData($customerData);
        if ($customerErrors !== true) {
            $errors = array_merge($customerErrors, $errors);
        } else {
            $customerForm->compactData($customerData);
            $customer->setPassword($request->getPost('password'));
            $customer->setConfirmation($request->getPost('confirmation'));
            $customerErrors = $customer->validate();
            if (is_array($customerErrors)) {
                $errors = array_merge($customerErrors, $errors);
            }
        }
        return $errors;
    }

    /**
     * Wholesale customer register create action
     */
    public function createWholesaleAction()
    {
        // if ($this->_getSession()->isLoggedIn()) {
        //     $this->_redirect('*/*');
        //     return;
        // }
        if ($this->_getSession()->isLoggedIn()) {
            $groupId = $this->_getSession()->getCustomerGroupId();
            $group = Mage::getModel('customer/group')->load($groupId);
            $code = strtolower($group->getCode());
            if ($code == 'wholesale') {
                $this->_redirect('*/*');
                return;
            }
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Wholesale customer register create post action
     */
    public function createWholesalePostAction()
    {
        $errUrl = $this->_getUrl('*/*/createwholesale', array('_secure' => true));

        if (!$this->_validateFormKey()) {
            $this->_redirectError($errUrl);
            return;
        }

        /** @var $session Mage_Customer_Model_Session */
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->_redirectError($errUrl);
            return;
        }

        if($this->getRequest()->getPost('group_id') == 2){
            $to = "wholesale@bulksupplements.com";
            $subject = "Wholesale Account Signup";

            $message = '<p>' . $this->getRequest()->getPost('firstname') . " " . $this->getRequest()->getPost('lastname') . ' has registered with the additional contact information.</p>';
            // $message .= '<h2>Billing Address</h2>';
            $message .= '<p><strong>Customer:</strong> ' . $this->getRequest()->getPost('firstname') . ' ' . $this->getRequest()->getPost('lastname') . '</p>';
            $message .= '<p><strong>Customer Email:</strong> ' . $this->getRequest()->getPost('email') . '</p>';
            $message .= '<h3 style="color: #FF7A01;">Billing Address</h3>';
            $company = $this->getRequest()->getPost('company');
            if (isset($company) && $company != '')
                $message .= '<p><strong>Company: </strong> ' . $company . '</p>';
            $message .= '<p><strong>Name: </strong> ' .$this->getRequest()->getPost('firstname') . " " . $this->getRequest()->getPost('lastname') . '</p>';

            $address = '';
            $street = $this->getRequest()->getPost('street');
            $region_id = $this->getRequest()->getPost('region_id');
            $country_id = $this->getRequest()->getPost('country_id');
            $country = Mage::getModel('directory/country')->loadByCode($country_id)->getName();
            $state = '';
            if (isset($region_id)) {
                $state = Mage::getModel('directory/region')->load($region_id)->getName();
            } else {
                $state = $this->getRequest()->getPost('region');
            }
            
            if (is_array($street)) {
                $address = implode(' ', $street);
            } else {
                $address = $street;
            }
            $address .= ', ' . $this->getRequest()->getPost('city') . ', ' . $this->getRequest()->getPost('postcode') . ', ' . $state . ', ' . $country;
            $message .= '<p><strong>Address: </strong> ' . $address . '</p>';

            $message .= '<p><strong>Telephone: </strong> ' . $this->getRequest()->getPost('telephone') . '</p>';
            $fax = $this->getRequest()->getPost('fax');
            if (isset($fax) && $fax != '')
                $message .= '<p><strong>Fax: </strong> ' . $fax . '</p>';

            $message .= '<h3 style="color: #FF7A01;">Shipping Address</h3>';
            $create_shipping_address = $this->getRequest()->getPost('create_shipping_address');
            if (isset($create_shipping_address) && $create_shipping_address == 0) {
                $message .= '<p><strong>Same as Billing Address</strong></p>';
            } else {
                $shipping_company = $this->getRequest()->getPost('shipping_company');
                if (isset($shipping_company) && $shipping_company != '')
                    $message .= '<p><strong>Company: </strong> ' . $shipping_company . '</p>';
                $message .= '<p><strong>Name: </strong> ' .$this->getRequest()->getPost('shipping_firstname') . " " . $this->getRequest()->getPost('shipping_lastname') . '</p>';

                $shipping_address = '';
                $shipping_street = $this->getRequest()->getPost('shipping_street');
                $shipping_region_id = $this->getRequest()->getPost('shipping_region_id');
                $shipping_country_id = $this->getRequest()->getPost('shipping_country_id');
                $shipping_country = Mage::getModel('directory/country')->loadByCode($shipping_country_id)->getName();
                $shipping_state = '';
                if (isset($shipping_region_id)) {
                    $shipping_state = Mage::getModel('directory/region')->load($shipping_region_id)->getName();
                } else {
                    $shipping_state = $this->getRequest()->getPost('shipping_region');
                }
                
                if (is_array($shipping_street)) {
                    $shipping_address = implode(' ', $shipping_street);
                } else {
                    $shipping_address = $shipping_street;
                }
                $shipping_address .= ', ' . $this->getRequest()->getPost('shipping_city') . ', ' . $this->getRequest()->getPost('shipping_postcode') . ', ' . $shipping_state . ', ' . $shipping_country;
                $message .= '<p><strong>Address: </strong> ' . $shipping_address . '</p>';
                $shipping_website = $this->getRequest()->getPost('shipping_website');
                if (isset($shipping_website) && $shipping_website != '')
                    $message .= '<p><strong>Website: </strong> ' . $shipping_website . '</p>';
                $shipping_sell_online = $this->getRequest()->getPost('shipping_sell_online');
                if (isset($shipping_sell_online)) {
                    $message .= '<p><strong>Do you currently sell online?</strong> ';
                    if ($shipping_sell_online == 0)
                        $message .= 'No</p>';
                    else
                        $message .= 'Yes</p>';
                }
            }

            $message .= '<h3 style="color: #FF7A01;">Additional Contact</h3>';
            $additional_name = $this->getRequest()->getPost('additional_name');
            if (isset($additional_name) && $additional_name != '')
                $message .= '<p><strong>Name: </strong> ' . $additional_name . '</p>';
            $additional_telephone = $this->getRequest()->getPost('additional_telephone');
            if (isset($additional_telephone) && $additional_telephone != '')
                $message .= '<p><strong>Telephone: </strong> ' . $additional_telephone . '</p>';

            $credit_terms = $this->getRequest()->getPost('credit_terms');
            if (isset($credit_terms)) {
                $message .= '<p><strong>Would you like to be considered for credit terms?</strong> ';
                if ($credit_terms == 0)
                    $message .= 'No</p>';
                else
                    $message .= 'Yes</p>';
            }
            $sell_products_online = $this->getRequest()->getPost('sell_products_online');
            if (isset($sell_products_online)) {
                $message .= '<p><strong>Will you be selling our products online?</strong> ';
                if ($sell_products_online == 0)
                    $message .= 'No</p>';
                else
                    $message .= 'Yes</p>';
            }
            
            $message .= '<p><strong>Please select the category that best applies to your organization.</strong></p>';
            $message .= '<p>';
            $tmp1 = array();
            $val = $this->getRequest()->getPost('manufacturer');
            if (isset($val))
                $tmp1[] = 'Manufacturer';
            $val = $this->getRequest()->getPost('distributor');
            if (isset($val))
                $tmp1[] = 'Distributor/Ingredient Supplier';
            $val = $this->getRequest()->getPost('marketing_branding');
            if (isset($val))
                $tmp1[] = 'Marketing/Branding';
            $val = $this->getRequest()->getPost('lab_services_research');
            if (isset($val))
                $tmp1[] = 'Lab Services/Research';
            $val = $this->getRequest()->getPost('contract_manufacturer');
            if (isset($val))
                $tmp1[] = 'Contract Manufacturer';
            $val = $this->getRequest()->getPost('other');
            if (isset($val))
                $tmp1[] = 'Other';
            $message .= implode(', ', $tmp1);
            $message .= '</p>';
            $message .= '<p><strong>How did you hear about BulkSupplements.com?</strong></p>';
            $message .= '<p>';
            $tmp2 = array();
            $val = $this->getRequest()->getPost('trade_show');
            if (isset($val))
                $tmp2[] = 'Trade Show';
            $val = $this->getRequest()->getPost('sales_rep');
            if (isset($val))
                $tmp2[] = 'Sales Rep';
            $val = $this->getRequest()->getPost('social_media');
            if (isset($val))
                $tmp2[] = 'Social Media';
            $val = $this->getRequest()->getPost('search_engine');
            if (isset($val))
                $tmp2[] = 'Search Engine';
            $val = $this->getRequest()->getPost('amazon');
            if (isset($val))
                $tmp2[] = 'Amazon';
            $val = $this->getRequest()->getPost('reference');
            if (isset($val))
                $tmp2[] = 'Reference';
            $message .= implode(', ', $tmp2);
            $message .= '</p>';
            $message .= '<br/><br/>The customer has registered as General. We need to set this customer as wholesale.';
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=ISO-8859-1" . "\r\n";
            $from = $this->getRequest()->getPost('firstname') . " " . $this->getRequest()->getPost('lastname') . '<' . $this->getRequest()->getPost('email') . '>';
            $headers .= 'From: ' . $from . "\r\n";
            $headers .= 'Reply-To: ' . $from . "\r\n";
            $headers .= 'Return-Path: ' . $this->getRequest()->getPost('email') . "\r\n";

            mail($to, $subject, $message, $headers, "-f" . $this->getRequest()->getPost('email'));

            Mage::getSingleton('core/session')->setThankyouWholesaleSignup('sent');
            $this->_redirectUrl($this->_getUrl('thankyouwholesalesignup'));
        } else {
            $this->_redirectError($errUrl);
        }

        // $customer = $this->_getCustomer();

        // try {
        //     $errors = $this->_getCustomerErrorsNew($customer);

        //     if (empty($errors)) {
        //         $customer->save();
        //         if($this->getRequest()->getPost('group_id') == 2){
        //             $to = "erik.wisell1016@gmail.com";
        //             $subject = "Wholesale Account Signup";

        //             $message = '<p>' . $this->getRequest()->getPost('firstname') . " " . $this->getRequest()->getPost('lastname') . ' has registered with the additional contact information.</p>';
        //             // $message .= '<h2>Billing Address</h2>';
        //             $message .= '<p><strong>Customer:</strong> ' . $this->getRequest()->getPost('firstname') . ' ' . $this->getRequest()->getPost('lastname') . '</p>';
        //             $message .= '<p><strong>Customer Email:</strong> ' . $this->getRequest()->getPost('email') . '</p>';
        //             $message .= '<h3 style="color: #FF7A01;">Billing Address</h3>';
        //             $company = $this->getRequest()->getPost('company');
        //             if (isset($company) && $company != '')
        //                 $message .= '<p><strong>Company: </strong> ' . $company . '</p>';
        //             $message .= '<p><strong>Name: </strong> ' .$this->getRequest()->getPost('firstname') . " " . $this->getRequest()->getPost('lastname') . '</p>';

        //             $address = '';
        //             $street = $this->getRequest()->getPost('street');
        //             $region_id = $this->getRequest()->getPost('region_id');
        //             $country_id = $this->getRequest()->getPost('country_id');
        //             $country = Mage::getModel('directory/country')->loadByCode($country_id)->getName();
        //             $state = '';
        //             if (isset($region_id)) {
        //                 $state = Mage::getModel('directory/region')->load($region_id)->getName();
        //             } else {
        //                 $state = $this->getRequest()->getPost('region');
        //             }
                    
        //             if (is_array($street)) {
        //                 $address = implode(' ', $street);
        //             } else {
        //                 $address = $street;
        //             }
        //             $address .= ', ' . $this->getRequest()->getPost('city') . ', ' . $this->getRequest()->getPost('postcode') . ', ' . $state . ', ' . $country;
        //             $message .= '<p><strong>Address: </strong> ' . $address . '</p>';

        //             $message .= '<p><strong>Telephone: </strong> ' . $this->getRequest()->getPost('telephone') . '</p>';
        //             $fax = $this->getRequest()->getPost('fax');
        //             if (isset($fax) && $fax != '')
        //                 $message .= '<p><strong>Fax: </strong> ' . $fax . '</p>';

        //             $message .= '<h3 style="color: #FF7A01;">Shipping Address</h3>';
        //             $shipping_company = $this->getRequest()->getPost('shipping_company');
        //             if (isset($shipping_company) && $shipping_company != '')
        //                 $message .= '<p><strong>Company: </strong> ' . $shipping_company . '</p>';
        //             $message .= '<p><strong>Name: </strong> ' .$this->getRequest()->getPost('shipping_firstname') . " " . $this->getRequest()->getPost('shipping_lastname') . '</p>';

        //             $shipping_address = '';
        //             $shipping_street = $this->getRequest()->getPost('shipping_street');
        //             $shipping_region_id = $this->getRequest()->getPost('shipping_region_id');
        //             $shipping_country_id = $this->getRequest()->getPost('shipping_country_id');
        //             $shipping_country = Mage::getModel('directory/country')->loadByCode($shipping_country_id)->getName();
        //             $shipping_state = '';
        //             if (isset($shipping_region_id)) {
        //                 $shipping_state = Mage::getModel('directory/region')->load($shipping_region_id)->getName();
        //             } else {
        //                 $shipping_state = $this->getRequest()->getPost('shipping_region');
        //             }
                    
        //             if (is_array($shipping_street)) {
        //                 $shipping_address = implode(' ', $shipping_street);
        //             } else {
        //                 $shipping_address = $shipping_street;
        //             }
        //             $shipping_address .= ', ' . $this->getRequest()->getPost('shipping_city') . ', ' . $this->getRequest()->getPost('shipping_postcode') . ', ' . $shipping_state . ', ' . $shipping_country;
        //             $message .= '<p><strong>Address: </strong> ' . $shipping_address . '</p>';
        //             $shipping_website = $this->getRequest()->getPost('shipping_website');
        //             if (isset($shipping_website) && $shipping_website != '')
        //                 $message .= '<p><strong>Website: </strong> ' . $shipping_website . '</p>';
        //             $shipping_sell_online = $this->getRequest()->getPost('shipping_sell_online');
        //             if (isset($shipping_sell_online)) {
        //                 $message .= '<p><strong>Do you currently sell online?</strong> ';
        //                 if ($shipping_sell_online == 0)
        //                     $message .= 'No</p>';
        //                 else
        //                     $message .= 'Yes</p>';
        //             }

        //             $message .= '<h3 style="color: #FF7A01;">Additional Contact</h3>';
        //             $additional_name = $this->getRequest()->getPost('additional_name');
        //             if (isset($additional_name) && $additional_name != '')
        //                 $message .= '<p><strong>Name: </strong> ' . $additional_name . '</p>';
        //             $additional_telephone = $this->getRequest()->getPost('additional_telephone');
        //             if (isset($additional_telephone) && $additional_telephone != '')
        //                 $message .= '<p><strong>Telephone: </strong> ' . $additional_telephone . '</p>';

        //             $credit_terms = $this->getRequest()->getPost('credit_terms');
        //             if (isset($credit_terms)) {
        //                 $message .= '<p><strong>Would you like to be considered for credit terms?</strong> ';
        //                 if ($credit_terms == 0)
        //                     $message .= 'No</p>';
        //                 else
        //                     $message .= 'Yes</p>';
        //             }
        //             $sell_products_online = $this->getRequest()->getPost('sell_products_online');
        //             if (isset($sell_products_online)) {
        //                 $message .= '<p><strong>Will you be selling our products online?</strong> ';
        //                 if ($sell_products_online == 0)
        //                     $message .= 'No</p>';
        //                 else
        //                     $message .= 'Yes</p>';
        //             }
                    
        //             $message .= '<p><strong>Please select the category that best applies to your organization.</strong></p>';
        //             $message .= '<p>';
        //             $tmp1 = array();
        //             $val = $this->getRequest()->getPost('buying_club');
        //             if (isset($val))
        //                 $tmp1[] = 'Buying Club';
        //             $val = $this->getRequest()->getPost('distributor');
        //             if (isset($val))
        //                 $tmp1[] = 'Distributor';
        //             $val = $this->getRequest()->getPost('food_service');
        //             if (isset($val))
        //                 $tmp1[] = 'Food Service';
        //             $val = $this->getRequest()->getPost('grocery');
        //             if (isset($val))
        //                 $tmp1[] = 'Grocery';
        //             $val = $this->getRequest()->getPost('internet_only_store');
        //             if (isset($val))
        //                 $tmp1[] = 'Internet-Only Store';
        //             $val = $this->getRequest()->getPost('mail_order');
        //             if (isset($val))
        //                 $tmp1[] = 'Mail Order';
        //             $val = $this->getRequest()->getPost('manufacturer');
        //             if (isset($val))
        //                 $tmp1[] = 'Manufacturer';
        //             $val = $this->getRequest()->getPost('practitioner_clinic');
        //             if (isset($val))
        //                 $tmp1[] = 'Practitioner/Clinic';
        //             $val = $this->getRequest()->getPost('retail_store');
        //             if (isset($val))
        //                 $tmp1[] = 'Retail Store';
        //             $message .= implode(', ', $tmp1);
        //             $message .= '</p>';
        //             $message .= '<p><strong>How did you hear about BulkSupplements.com?</strong></p>';
        //             $message .= '<p>';
        //             $tmp2 = array();
        //             $val = $this->getRequest()->getPost('american_herbal_product_association');
        //             if (isset($val))
        //                 $tmp2[] = 'American Herbal Product Association';
        //             $val = $this->getRequest()->getPost('friend');
        //             if (isset($val))
        //                 $tmp2[] = 'Friend';
        //             $val = $this->getRequest()->getPost('herb_research_foundation');
        //             if (isset($val))
        //                 $tmp2[] = 'Herb Research Foundation';
        //             $val = $this->getRequest()->getPost('internet_search');
        //             if (isset($val))
        //                 $tmp2[] = 'Internet Search';
        //             $val = $this->getRequest()->getPost('mary_janes_farm');
        //             if (isset($val))
        //                 $tmp2[] = 'Mary Janes Farm';
        //             $val = $this->getRequest()->getPost('natural_news');
        //             if (isset($val))
        //                 $tmp2[] = 'Natural News';
        //             $val = $this->getRequest()->getPost('retail_store');
        //             if (isset($val))
        //                 $tmp2[] = 'Retail Store';
        //             $val = $this->getRequest()->getPost('united_plant_savers');
        //             if (isset($val))
        //                 $tmp2[] = 'United Plant Savers';
        //             $val = $this->getRequest()->getPost('vitamin_retailer');
        //             if (isset($val))
        //                 $tmp2[] = 'Vitamin Retailer';
        //             $val = $this->getRequest()->getPost('whole_foods');
        //             if (isset($val))
        //                 $tmp2[] = 'Whole Foods';
        //             $message .= implode(', ', $tmp2);
        //             $message .= '</p>';
        //             $message .= '<br/><br/>The customer has registered as General. We need to set this customer as wholesale.';
        //             // $street = $this->getRequest()->getPost('street');
        //             // $street = is_array($street) ? implode("<br />", $street) : $street;
        //             // $message .= '<p><strong>Street:</strong></p>';
        //             // $message .= '<p>' . $street . '</p>';
        //             // $message .= '<p>' . $this->getRequest()->getPost('firstname') . '</p>';
                    
        //             $headers = "MIME-Version: 1.0" . "\r\n";
        //             $headers .= "Content-type: text/html; charset=ISO-8859-1" . "\r\n";
        //             $from = $this->getRequest()->getPost('firstname') . " " . $this->getRequest()->getPost('lastname') . '<' . $this->getRequest()->getPost('email') . '>';
        //             $headers .= 'From: ' . $from . "\r\n";
        //             $headers .= 'Reply-To: ' . $from . "\r\n";
        //             $headers .= 'Return-Path: ' . $this->getRequest()->getPost('email') . "\r\n";

        //             mail($to, $subject, $message, $headers, "-f" . $this->getRequest()->getPost('email'));
        //         }
        //         $this->_dispatchRegisterSuccess($customer);
        //         $this->_successProcessRegistration($customer, true);
        //         return;
        //     } else {
        //         $this->_addSessionError($errors);
        //     }
        // } catch (Mage_Core_Exception $e) {
        //     $session->setCustomerFormData($this->getRequest()->getPost());
        //     if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
        //         $url = $this->_getUrl('customer/account/forgotpassword');
        //         $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
        //     } else {
        //         $message = $this->_escapeHtml($e->getMessage());
        //     }
        //     $session->addError($message);
        // } catch (Exception $e) {
        //     $session->setCustomerFormData($this->getRequest()->getPost());
        //     $session->addException($e, $this->__('Cannot save the customer.'));
        // }

        // $this->_redirectError($errUrl);
    }
}