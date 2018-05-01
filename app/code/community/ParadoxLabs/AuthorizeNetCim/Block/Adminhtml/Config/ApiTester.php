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

class ParadoxLabs_AuthorizeNetCim_Block_Adminhtml_Config_ApiTester extends ParadoxLabs_TokenBase_Block_Adminhtml_Config_ApiTest
{
	protected $_code	= 'authnetcim';
	
	/**
	 * Test the API connection and report common errors.
	 */
	protected function _testApi() {
		/**
		 * Self-healing: Fix common setup problems. Convenient spot.
		 */
		if( Mage::registry('authnetcim_self_healed') !== 1 ) {
			try {
				$setup = Mage::getModel( 'eav/entity_setup', 'core_setup' );
				
				/**
				 * authnetcim_profile_id customer attribute
				 */
				if( $setup->getAttributeId('customer', 'authnetcim_profile_id') === false ) {
					$setup->addAttribute(
						'customer',
						'authnetcim_profile_id',
						array(
							'label'				=> 'Authorize.Net CIM: Profile ID',
							'type'				=> 'varchar',
							'input'				=> 'text',
							'default'			=> '',
							'position'			=> 70,
							'visible'			=> true,
							'required'			=> false,
							'user_defined'		=> true,
							'visible_on_front'	=> false,
						)
					);
				}
				
				/**
				 * authnetcim_profile_version customer attribute
				 */
				if( $setup->getAttributeId('customer', 'authnetcim_profile_version') === false ) {
					$setup->addAttribute(
						'customer',
						'authnetcim_profile_version',
						array(
							'label'				=> 'Authorize.Net CIM: Profile version (for updating legacy data)',
							'type'				=> 'int',
							'input'				=> 'text',
							'default'			=> '100',
							'position'			=> 71,
							'visible'			=> true,
							'required'			=> false,
							'user_defined'		=> true,
							'visible_on_front'	=> false,
						)
					);
				}
				
				Mage::register( 'authnetcim_self_healed', 1 );
			}
			catch( Exception $e ) {
				Mage::helper('tokenbase')->log( $this->_code, (string)$e );
			}
		}
		
		/**
		 * Test the API connection.
		 */
		$method = Mage::helper('payment')->getMethodInstance( $this->_code );
		$method->setStore( $this->_getStoreId() );
		
		// Don't bother if details aren't entered.
		if( $method->getConfigData('login') == '' || $method->getConfigData('trans_key') == '' ) {
			return 'Enter API credentials and save to test.';
		}
		
		$gateway = $method->gateway();
		
		try {
			// Run the test call -- simple profile request. It won't exist, that's okay.
			$gateway->setParameter( 'customerProfileId', '1' );
			$gateway->getCustomerProfile();
			
			return 'Authorize.Net CIM connected successfully.';
		}
		catch( Exception $e ) {
			/**
			 * Handle common configuration errors.
			 */
			
			$result		= $gateway->getLastResponse();
			$errorCode	= $result['messages']['message']['code'];
			
			// Bad login ID / trans key
			if( in_array( $errorCode, array( 'E00005', 'E00006', 'E00007', 'E00008' ) ) ) {
				return sprintf( 'Your API credentials are invalid. (%s)', $errorCode );
			}
			// Test mode active
			elseif( $errorCode == 'E00009' ) {
				return sprintf( 'Your account has test mode enabled. It must be disabled for CIM to work properly. (%s)', $errorCode );
			}
			// CIM not enabled
			elseif( $errorCode == 'E00044' ) {
				return sprintf( 'Your account does not have CIM enabled. Please contact your Authorize.Net support rep to resolve this. (%s)', $errorCode );
			}
			
			return $e->getMessage();
		}
	}
}
