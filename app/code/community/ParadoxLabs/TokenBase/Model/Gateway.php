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

/**
 * Generic gateway methods, logging, exceptions, w/e
 */
abstract class ParadoxLabs_TokenBase_Model_Gateway extends Mage_Core_Model_Abstract
{
	protected $_code		= 'tokenbase';
	
	protected $_endpoint;
	protected $_secretKey;
	protected $_testMode;
	protected $_verifySsl;
	
	/**
	 * $_fields sets validation for each input.
	 * 
	 * key => array(
	 *    'maxLength' => int,
	 *    'noSymbols' => true|false,
	 *    'charMask'  => (allowed characters in regex form),
	 *    'enum'      => array( values )
	 * )
	 */
	protected $_fields		= array();
	
	/**
	 * These hold parameters for each request.
	 */
	protected $_params		= array();
	protected $_defaults	= array();
	
	protected $_lastRequest;
	protected $_lastResponse;
	
	protected $_lineItems	= null;
	
	protected $_log			= '';
	
	protected $_endpointLive	= '';
	protected $_endpointTest	= '';
	
	/**
	 * Initialize the gateway.
	 * Input is taken as an array for greater flexibility.
	 */
	public function init( array $parameters )
	{
		$this->_secretKey	= isset( $parameters['secret_key'] ) ? $parameters['secret_key'] : '';
		$this->_testMode	= isset( $parameters['test_mode'] ) ? (bool)$parameters['test_mode'] : false;
		$this->_verifySsl	= isset( $parameters['verify_ssl'] ) ? (bool)$parameters['verify_ssl'] : true;
		
		$this->_defaults 	= array(
			'login'		=> $parameters['login'],
			'password'	=> $parameters['password']
		);
		
		if( isset( $parameters['endpoint'] ) ) {
			$this->_endpoint = $parameters['endpoint'];
		}
		else {
			$this->_endpoint = ( $this->_testMode === true ? $this->_endpointTest : $this->_endpointLive );
		}
		
		$this->clearParameters();
		
		return $this;
	}
	
	/**
	 * Set the API parameters back to defaults, clearing any runtime values.
	 */
	public function clearParameters()
	{
		$this->_params		= $this->_defaults;
		$this->_log			= '';
		$this->_lineItems	= null;
		
		return $this;
	}
	
	/**
	 * Set a parameter.
	 */
	public function setParameter( $key, $val )
	{
		if( !empty($val) ) {
			/**
			 * Make sure we know this parameter
			 */
			if( in_array( $key, array_keys( $this->_fields ) ) ) {
				/**
				 * Run validations
				 */
				
				if( isset( $this->_fields[ $key ]['noSymbols'] ) && $this->_fields[ $key ]['noSymbols'] === true ) {
					/**
					 * Convert special characters to simple ascii equivalent
					 */
					$val = htmlentities( $val, ENT_QUOTES, 'UTF-8' );
					$val = preg_replace( '/&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);/i', '$1', $val );
					$val = preg_replace( array( '/[^0-9a-z \.]/i', '/-+/' ), ' ', $val );
					$val = trim( $val );
				}
				
				if( isset( $this->_fields[ $key ]['charMask'] ) ) {
					/**
					 * Apply a regex character filter to the input.
					 */
					$val = preg_replace( '/[^' . $this->_fields[ $key ]['charMask'] . ']/', '', $val );
				}
				
				if( isset( $this->_fields[ $key ]['maxLength'] ) && $this->_fields[ $key ]['maxLength'] > 0 ) {
					/**
					 * Truncate if the value is too long
					 */
					$this->_params[ $key ] = substr( $val, 0, $this->_fields[ $key ]['maxLength'] );
				}
				elseif( isset( $this->_fields[ $key ]['enum'] ) ) {
					/**
					 * Error if value is not on the allowed list
					 */
					if( in_array( $val, $this->_fields[ $key ]['enum'] ) ) {
						$this->_params[ $key ] = $val;
					}
					else {
						Mage::throwException( Mage::helper('tokenbase')->__( sprintf( "Payment Gateway: Invalid value for '%s': '%s'", $key, $val ) ) );
					}
				}
				else {
					$this->_params[ $key ] = $val;
				}
			}
			else {
				Mage::throwException( Mage::helper('tokenbase')->__( sprintf( "Payment Gateway: Unknown parameter '%s'", $key ) ) );
			}
		}
		
		return $this;
	}
	
	/**
	 * Get parameters. Debugging purposes.
	 * 
	 * Implementation should mask or erase any confidential data from the response.
	 * Card number, CVV, and password should never be logged in full.
	 */
	public function getParameters()
	{
		return $this->_params;
	}
	
	/**
	 * Get a single parameter
	 */
	public function getParameter( $key, $default='' )
	{
		return ( isset( $this->_params[ $key ] ) ? $this->_params[ $key ] : $default );
	}
	
	/**
	 * Check whether parameter exists
	 */
	public function hasParameter( $key )
	{
		return ( isset( $this->_params[ $key ] ) && !empty( $this->_params[ $key ] ) ? true : false );
	}
	
	/**
	 * Get the last response value.
	 */
	public function getLastResponse()
	{
		return $this->_lastResponse;
	}
	
	/**
	 * Print stored logs to the gateway log.
	 */
	public function logLogs()
	{
		Mage::helper('tokenbase')->log( $this->_code, $this->_log );
		
		return $this;
	}
	
	/**
	 * Add line items, to be sent with relevant transactions.
	 * Input should be a collection of items.
	 */
	public function setLineItems( $items )
	{
		$this->_lineItems = $items;
		
		return $this;
	}
	
	/**
	 * Format decimals to the appropriate precision.
	 */
	public static function formatAmount( $amount )
	{
		return sprintf( "%01.2f", (float) $amount );
	}
	
	/**
	 * Pull a value from a nested array safely (without notices, default fallback)
	 *
	 * @param  array  $data    source array
	 * @param  string $path    path to pull, separated by slashes
	 * @param  string $default default response (if key DNE)
	 * @return mixed           target value or default
	 */
	protected function _getArrayValue( $data, $path, $default='' )
	{
		$path	= explode( '/', $path );
		$val	=& $data;
		
		foreach( $path as $key ) {
			if( !isset( $val[ $key ] ) ) {
				return $default;
			}
			
			$val =& $val[ $key ];
		}
		
		return $val;
	}
	
	/**
	 * Convert array to XML string. See tokenbase/gateway_xml
	 */
	protected function _arrayToXml( $rootName, $array )
	{
		$xml = Mage::getModel('tokenbase/gateway_xml')->createXML( $rootName, $array );
		
		return $xml->saveXML();
	}
	
	/**
	 * Convert XML string to array. See tokenbase/gateway_xml
	 */
	protected function _xmlToArray( $xml )
	{
		return Mage::getModel('tokenbase/gateway_xml')->createArray( $xml );
	}
	
	/**
	 * These should be implemented by the child gateway.
	 */
	
	public function setCard( ParadoxLabs_TokenBase_Model_Card $card )
	{
		return parent::setCard( $card );
	}
	
	abstract public function authorize( $payment, $amount );
	abstract public function capture( $payment, $amount, $realTransactionId=null );
	abstract public function refund( $payment, $amount, $realTransactionId=null );
	abstract public function void( $payment );
	abstract public function fraudUpdate( $payment, $transaction );
}
