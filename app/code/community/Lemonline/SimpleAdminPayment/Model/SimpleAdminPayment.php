<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 */


class Lemonline_SimpleAdminPayment_Model_SimpleAdminPayment extends Mage_Payment_Model_Method_Abstract
{

	protected $_code  = 'simpleadminpayment';

	protected $_canUseCheckout = false;
	protected $_canUseForMultishipping  = false;

	/**
	 * (non-PHPdoc)
	 * @see app/code/core/Mage/Payment/Model/Method/Mage_Payment_Model_Method_Abstract#authorize($payment, $amount)
	 */
	public function authorize(Varien_Object $payment, $total) {
		if($this->getConfigData('capture_auto') && $payment->getOrder()->canInvoice()) {
			$invoice = $payment->getOrder()->prepareInvoice();
			$invoice->register();
			$payment->getOrder()->addRelatedObject($invoice);
		}
		if($this->getConfigData('shipment_auto') && $payment->getOrder()->canShip()) {
			$shipment = $payment->getOrder()->prepareShipment();
			$shipment->register();
			$payment->getOrder()->addRelatedObject($shipment);
		}
		return $this;
	}

	/**
	 * Check whether payment method can be used
	 *
	 * TODO: payment method instance is not supposed to know about quote
	 *
	 * @param Mage_Sales_Model_Quote|null $quote
	 *
	 * @return bool
	 */
	public function isAvailable($quote = null)
	{
		$checkResult = new StdClass;
		$isActive = (bool)(int)$this->getConfigData('active', $quote ? $quote->getStoreId() : null);
		$checkResult->isAvailable = $isActive;
		$checkResult->isDeniedInConfig = !$isActive; // for future use in observers
		Mage::dispatchEvent('payment_method_is_active', array(
			'result'          => $checkResult,
			'method_instance' => $this,
			'quote'           => $quote,
		));

		// disable method if it cannot implement recurring profiles management and there are recurring items in quote
		if ($checkResult->isAvailable) {
			$implementsRecurring = $this->canManageRecurringProfiles();
			// the $quote->hasRecurringItems() causes big performance impact, thus it has to be called last
			if ($quote && !$implementsRecurring && $quote->hasRecurringItems()) {
				$checkResult->isAvailable = false;
			}
		}

		// Matthew : start
		if(Mage::getSingleton('admin/session')->isLoggedIn()) {
			$user = Mage::getSingleton('admin/session')->getUser();
			$roleId = implode('', $user->getRoles());
			$roleName = Mage::getModel('admin/roles')->load($roleId)->getRoleName();
			if ($roleName != "Administrators" && $roleName != "Customer Service")
				$checkResult->isAvailable = false;
		}
		// end: Matthew

		return $checkResult->isAvailable;
	}

}
