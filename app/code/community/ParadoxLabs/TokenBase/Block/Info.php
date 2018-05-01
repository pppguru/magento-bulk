<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 * 
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 * 
 * Want to customize or need help with your store?
 *  Phone: 717-431-3330
 *  Email: sales@paradoxlabs.com
 *
 * @category	ParadoxLabs
 * @package		TokenBase
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_TokenBase_Block_Info extends Mage_Payment_Block_Info_Cc
{
	protected $_isEcheck = false;
	
	protected function _prepareSpecificInformation($transport = null)
	{
		if( null !== $this->_paymentSpecificInformation ) {
			return $this->_paymentSpecificInformation;
		}
		
		$transport = Mage_Payment_Block_Info::_prepareSpecificInformation($transport);
		$data = array();
		
		Mage::dispatchEvent( 'tokenbase_before_load_payment_info',
			array(
				'method'	=> $this->getInfo()->getMethod(),
				'customer'	=> Mage::helper('tokenbase')->getCurrentCustomer(),
				'transport'	=> $transport,
				'info'		=> $this->getInfo(),
			) );
		
		
		// If this is an eCheck, show different info.
		if( $this->_isEcheck() === true ) {
			if( $this->getInfo()->getEcheckBankName() != '' ) {
				$data[Mage::helper('payment')->__('Bank Name')] = $this->getInfo()->getEcheckBankName();
			}
			elseif( $this->getInfo()->getAdditionalInformation('echeck_bank_name') != '' ) {
				$data[Mage::helper('payment')->__('Bank Name')] = $this->getInfo()->getAdditionalInformation('echeck_bank_name');
			}
			
			$data[Mage::helper('payment')->__('Account Number')] = sprintf( 'x-%s', $this->getInfo()->getAdditionalInformation('echeck_account_number_last4') );
		}
		else {
			$ccType = $this->getCcTypeName();
			if( !empty( $ccType ) && $ccType != 'N/A' ) {
				$data[Mage::helper('payment')->__('Credit Card Type')] = $ccType;
			}
			
			if( $this->getInfo()->getCcLast4() ) {
				$data[Mage::helper('payment')->__('Credit Card Number')] = sprintf( 'XXXX-%s', $this->getInfo()->getCcLast4() );
			}
		}
		
		// If this is admin, show different info.
		if( Mage::app()->getStore()->isAdmin() ) {
			$data[Mage::helper('payment')->__('Transaction ID')]	= $this->getInfo()->getAdditionalInformation('transaction_id');
		}
		
		$transport->setData( array_merge( $data, $transport->getData() ) );
		
		Mage::dispatchEvent( 'tokenbase_after_load_payment_info',
			array(
				'method'	=> $this->getInfo()->getMethod(),
				'customer'	=> Mage::helper('tokenbase')->getCurrentCustomer(),
				'transport'	=> $transport,
				'info'		=> $this->getInfo(),
			) );
		
		return $transport;
	}
	
	protected function _isEcheck()
	{
		return $this->_isEcheck;
	}
}
