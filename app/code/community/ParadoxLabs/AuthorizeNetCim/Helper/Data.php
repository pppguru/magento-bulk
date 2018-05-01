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

class ParadoxLabs_AuthorizeNetCim_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $_avsResponses = array(
		'B'	=> 'No address submitted; could not perform AVS check.',
		'E'	=> 'AVS data invalid',
		'R'	=> 'AVS unavailable',
		'G'	=> 'AVS not supported',
		'U'	=> 'AVS unavailable',
		'S'	=> 'AVS not supported',
		'N'	=> 'Street and zipcode do not match.',
		'A'	=> 'Street matches; zipcode does not.',
		'Z'	=> '5-digit zip matches; street does not.',
		'W'	=> '9-digit zip matches; street does not.',
		'Y'	=> 'Perfect match',
		'X'	=> 'Perfect match',
		'P' => 'N/A',
	);
	
	protected $_ccvResponses = array(
		'M'	=> 'Passed',
		'N' => 'Failed',
		'P'	=> 'Not processed',
		'S'	=> 'Not received',
		'U'	=> 'N/A',
	);
	
	protected $_cavvResponses = array(
		'0'	=> 'Not validated; bad data',
		'1'	=> 'Failed',
		'2'	=> 'Passed',
		'3'	=> 'CAVV unavailable',
		'4'	=> 'CAVV unavailable',
		'7'	=> 'Failed',
		'8'	=> 'Passed',
		'9'	=> 'Failed (issuer unavailable)',
		'A'	=> 'Passed (issuer unavailable)',
		'B'	=> 'Passed (info only)',
	);
	
	protected $_cimCardTypeMap = array(
		'American Express'	=> 'AE',
		'Discover'			=> 'DI',
		'Diners Club'		=> 'DC',
		'JCB'				=> 'JCB',
		'MasterCard'		=> 'MC',
		'Visa'				=> 'VI',
	);
	
	protected $_achAccountTypes = array(
		'checking'			=> 'Checking',
		'savings'			=> 'Savings',
		'businessChecking'	=> 'Business Checking',
	);
	
	/**
	 * Translate AVS response codes shown on admin order pages.
	 */
	public function translateAvs( $code )
	{
		if( isset( $this->_avsResponses[ $code ] ) ) {
			return $this->__( sprintf( '%s (%s)', $code, $this->_avsResponses[ $code ] ) );
		}
		
		return $code;
	}
	
	/**
	 * Translate CCV response codes shown on admin order pages.
	 */
	public function translateCcv( $code )
	{
		if( isset( $this->_ccvResponses[ $code ] ) ) {
			return $this->__( sprintf( '%s (%s)', $code, $this->_ccvResponses[ $code ] ) );
		}
		
		return $code;
	}
	
	/**
	 * Translate CAVV response codes shown on admin order pages.
	 */
	public function translateCavv( $code )
	{
		if( isset( $this->_cavvResponses[ $code ] ) ) {
			return $this->__( sprintf( '%s (%s)', $code, $this->_cavvResponses[ $code ] ) );
		}
		
		return $code;
	}
	
	/**
	 * Map CC Type to Magento's.
	 */
	public function mapCcTypeToMagento( $type )
	{
		if( !empty( $type ) && isset( $this->_cimCardTypeMap[ $type ] ) ) {
			return $this->_cimCardTypeMap[ $type ];
		}
		
		return null;
	}
	
	/**
	 * Return valid ACH account types.
	 */
	public function getAchAccountTypes( $code=null )
	{
		if( !is_null( $code ) ) {
			if( isset( $this->_achAccountTypes[ $code ] ) ) {
				return $this->_achAccountTypes[ $code ];
			}
			
			return $code;
		}
		
		return $this->_achAccountTypes;
	}
}
