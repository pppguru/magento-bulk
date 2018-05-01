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

class ParadoxLabs_AuthorizeNetCim_Model_Ach_Gateway extends ParadoxLabs_AuthorizeNetCim_Model_Gateway
{
	protected $_code		= 'authnetcim_ach';
	
	/**
	 * Turn transaction results and directResponse into a usable object.
	 */
	protected function _interpretTransaction( $transactionResult )
	{
		$response = parent::_interpretTransaction( $transactionResult );
		
		if( $response->getAuthCode() == '' && $response->getMethod() == 'ECHECK' ) {
			$response->setAuthCode('ACH');
		}
		
		return $response;
	}
	
	/**
	 * Find a duplicate CIM record matching the one we just tried to create.
	 */
	public function findDuplicateCard()
	{
		$profile			= $this->getCustomerProfile();
		$accountLastFour	= substr( $this->getParameter('accountNumber'), -4 );
		$routingLastFour	= substr( $this->getParameter('routingNumber'), -4 );
		
		if( isset( $profile['profile']['paymentProfiles'] ) && count( $profile['profile']['paymentProfiles'] ) > 0 ) {
			// If there's only one, just stop. It has to be the match.
			if( isset( $profile['profile']['paymentProfiles']['billTo'] ) ) {
				$card = $profile['profile']['paymentProfiles'];
				return $card['customerPaymentProfileId'];
			}
			else {
				// Otherwise, compare end of routing number and account number for each until one matches.
				foreach( $profile['profile']['paymentProfiles'] as $card ) {
					if( isset( $card['payment']['bankAccount'] ) 
						&& $accountLastFour == substr( $card['payment']['bankAccount']['accountNumber'], -4 )
						&& $routingLastFour == substr( $card['payment']['bankAccount']['routingNumber'], -4 ) ) {
						return $card['customerPaymentProfileId'];
					}
				}
			}
		}
		
		return false;
	}
}
