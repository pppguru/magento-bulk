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
 * @package		AuthorizeNetCim
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_AuthorizeNetCim_Model_Ach_Method extends ParadoxLabs_AuthorizeNetCim_Model_Method
{
	protected $_formBlockType				= 'authnetcim_ach/form';
	protected $_infoBlockType				= 'authnetcim_ach/info';
	protected $_code						= 'authnetcim_ach';
	
	protected $_achFields					= array( 'echeck_account_name', 'echeck_bank_name', 'echeck_routing_no', 'echeck_account_no', 'echeck_account_type' );
	
	
	/**
	 * Payment method available? Bypassing CC type check.
	 */
	public function isAvailable( $quote=null )
	{
		if( Mage_Payment_Model_Method_Abstract::isAvailable() ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Update info during the checkout process.
	 */
	public function assignData( $data )
	{
		if( !($data instanceof Varien_Object) ) {
			$data = new Varien_Object( $data );
		}
		
		parent::assignData( $data );
		
		foreach( $this->_achFields as $field ) {
			if( $data->hasData( $field ) && $data->getData( $field ) != '' ) {
				$this->getInfoInstance()->setData( $field, $data->getData( $field ) );
				
				if( $field != 'echeck_routing_no' && $field != 'echeck_account_no' ) {
					$this->getInfoInstance()->setAdditionalInformation( $field, $data->getData( $field ) );
				}
			}
		}
		
		if( $data->getEcheckRoutingNo() != '' ) {
			$this->getInfoInstance()->setEcheckRoutingNumber( substr( $data->getEcheckRoutingNo(), -4 ) );
		}
		
		if( $data->getEcheckAccountNo() != '' ) {
			$last4 = substr( $data->getEcheckAccountNo(), -4 );
			
			$this->getInfoInstance()->setCcLast4( $last4 );
			$this->getInfoInstance()->setAdditionalInformation( 'echeck_account_number_last4', $last4 );
		}
		
		return $this;
	}
	
	/**
	 * Set the current payment card
	 */
	public function setCard( ParadoxLabs_TokenBase_Model_Card $card )
	{
		parent::setCard( $card );
		
		foreach( $this->_achFields as $field ) {
			if( $card->getAdditional( $field ) ) {
				$this->getInfoInstance()->setData( $field, $card->getAdditional( $field ) );
			}
		}
		
		$this->getInfoInstance()->setEcheckRoutingNumber( $card->getAdditional('echeck_routing_number_last4') );
		$this->getInfoInstance()->setAdditionalInformation( 'echeck_account_number_last4', $card->getAdditional('echeck_account_number_last4') );
		
		return $this;
	}
	
	/**
	 * Validate the transaction inputs.
	 */
	public function validate()
	{
		$this->_log( sprintf( 'validate(%s)', $this->getInfoInstance()->getCardId() ) );
		
		/**
		 * If no tokenbase ID, we must have a new card. Make sure all the details look valid.
		 */
		if( $this->getInfoInstance()->hasTokenbaseId() === false ) {
			// Fields all present?
			foreach( $this->_achFields as $field ) {
				$value = trim( $this->getInfoInstance()->getData( $field ) );
				
				if( empty( $value ) ) {
					throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Please complete all required fields: '.$field) );
					break;
				}
			}
			
			// Field lengths?
			if( strlen( $this->getInfoInstance()->getData('echeck_account_name') ) > 22 ) {
				throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Please limit your account name to 22 characters.') );
			}
			elseif( strlen( $this->getInfoInstance()->getData('echeck_routing_no') ) != 9 ) {
				throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Your routing number must be 9 digits long. Please recheck the value you entered.') );
			}
			elseif( strlen( $this->getInfoInstance()->getData('echeck_account_no') ) < 5 || strlen( $this->getInfoInstance()->getData('echeck_account_no') ) > 17 ) {
				throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Your account number must be between 5 and 17 digits. Please recheck the value you entered.') );
			}
			
			// Data types?
			if( !is_numeric( $this->getInfoInstance()->getData('echeck_routing_no') ) ) {
				throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Your routing number must be 9 digits long. Please recheck the value you entered.') );
			}
			elseif( !is_numeric( $this->getInfoInstance()->getData('echeck_account_no') ) ) {
				throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Your account number must be between 5 and 17 digits. Please recheck the value you entered.') );
			}
			
			return Mage_Payment_Model_Method_Abstract::validate();
		}
		/**
		 * If there is an ID, this might be an edit. Validate there too, as much as we can.
		 */
		else {
			if( $this->getInfoInstance()->getData('echeck_account_name') != '' && strlen( $this->getInfoInstance()->getData('echeck_account_name') ) > 22 ) {
				throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Please limit your account name to 22 characters.') );
			}
			
			if( $this->getInfoInstance()->getData('echeck_routing_no') != '' && substr( $this->getInfoInstance()->getData('echeck_routing_no'), 0, 4 ) != 'XXXX' ) {
				if( strlen( $this->getInfoInstance()->getData('echeck_routing_no') ) != 9 
					|| !is_numeric( $this->getInfoInstance()->getData('echeck_routing_no') ) ) {
					throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Your routing number must be 9 digits long. Please recheck the value you entered.') );
				}
			}
			
			if( $this->getInfoInstance()->getData('echeck_account_no') != '' && substr( $this->getInfoInstance()->getData('echeck_account_no'), 0, 4 ) != 'XXXX' ) {
				if( strlen( $this->getInfoInstance()->getData('echeck_account_no') ) < 5 
					|| strlen( $this->getInfoInstance()->getData('echeck_account_no') ) > 17
					|| !is_numeric( $this->getInfoInstance()->getData('echeck_account_no') ) ) {
					throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Your account number must be between 5 and 17 digits. Please recheck the value you entered.') );
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Return boolean whether given payment object includes new card info.
	 */
	protected function _paymentContainsCard( Varien_Object $payment )
	{
		if( strlen( $payment->getEcheckRoutingNo() ) == 9 && strlen( $payment->getEcheckAccountNo() ) >= 5 ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Save type for legacy cards. Legacy CIM method, not needed for ACH.
	 */
	protected function _fixLegacyCcType( $payment, $response )
	{
		return $payment;
	}
}
