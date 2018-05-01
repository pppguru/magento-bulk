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

class ParadoxLabs_AuthorizeNetCim_Model_Gateway extends ParadoxLabs_TokenBase_Model_Gateway
{
	/**
	 * Authorize.Net registered solution ID
	 *
	 * @var string
	 */
	const SOLUTION_ID			= 'A1000133';
	
	protected $_code			= 'authnetcim';
	
	protected $_endpointLive	= 'https://api2.authorize.net/xml/v1/request.api';
	protected $_endpointTest	= 'https://apitest.authorize.net/xml/v1/request.api';
	
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
	protected $_fields			= array(
		'amount'					=> array(  ),
		'accountNumber'				=> array( 'maxLength' => 17, 'charMask' => '\d' ),
		'accountType'				=> array( 'enum' => array( 'checking', 'savings', 'businessChecking' ) ),
		'allowPartialAuth'          => array( 'enum' => array( 'true', 'false' ) ),
		'approvalCode'				=> array( 'maxLength' ),
		'bankName'					=> array( 'maxLength' => 50 ),
		'billToAddress'				=> array( 'maxLength' => 60, 'noSymbols' => true ),
		'billToCity'				=> array( 'maxLength' => 40, 'noSymbols' => true ),
		'billToCompany'				=> array( 'maxLength' => 50, 'noSymbols' => true ),
		'billToCountry'				=> array( 'maxLength' => 60, 'noSymbols' => true ),
		'billToFaxNumber'			=> array( 'maxLength' => 25, 'charMask' => '\d\(\)\-\.' ),
		'billToFirstName'			=> array( 'maxLength' => 50, 'noSymbols' => true ),
		'billToLastName'			=> array( 'maxLength' => 50, 'noSymbols' => true ),
		'billToPhoneNumber'			=> array( 'maxLength' => 25, 'charMask' => '\d\(\)\-\.' ),
		'billToState'				=> array( 'maxLength' => 40, 'noSymbols' => true ),
		'billToZip'					=> array( 'maxLength' => 20, 'noSymbols' => true ),
		'cardCode'					=> array( 'maxLength' => 4, 'charMask' => '\d' ),
		'cardNumber'				=> array( 'maxLength' => 16, 'charMask' => 'X\d' ),
		'customerIp'				=> array(  ),
		'customerPaymentProfileId'	=> array(  ),
		'customerProfileId'			=> array(  ),
		'customerShippingAddressId'	=> array(  ),
		'customerType'				=> array( 'enum' => array( 'individual', 'business' ) ),
		'description'				=> array( 'maxLength' => 255 ),
		'duplicateWindow'           => array( 'charMask' => '\d' ),
		'dutyAmount'				=> array(  ),
		'dutyDescription'			=> array( 'maxLength' => 255 ),
		'dutyName'					=> array( 'maxLength' => 31 ),
		'echeckType'				=> array( 'enum' => array( 'CCD', 'PPD', 'TEL', 'WEB', 'ARC', 'BOC' ) ),
		'email'						=> array( 'maxLength' => 255 ),
		'emailCustomer'             => array( 'enum' => array( 'true', 'false' ) ),
		'expirationDate'			=> array( 'maxLength' => 7 ),
		'invoiceNumber'				=> array( 'maxLength' => 20, 'noSymbols' => true ),
		'itemName'					=> array( 'maxLength' => 31, 'noSymbols' => true ),
		'loginId'					=> array( 'maxLength' => 20 ),
		'merchantCustomerId'		=> array( 'maxLength' => 20 ),
		'nameOnAccount'				=> array( 'maxLength' => 22 ),
		'purchaseOrderNumber'		=> array( 'maxLength' => 25, 'noSymbols' => true ),
		'recurringBilling'			=> array( 'enum' => array( 'true', 'false' ) ),
		'refId'						=> array( 'maxLength' => 20 ),
		'routingNumber'				=> array( 'maxLength' => 9, 'charMask' => '\d' ),
		'shipAmount'				=> array(  ),
		'shipDescription'			=> array( 'maxLength' => 255 ),
		'shipName'					=> array( 'maxLength' => 31 ),
		'shipToAddress'				=> array( 'maxLength' => 60, 'noSymbols' => true ),
		'shipToCity'				=> array( 'maxLength' => 40, 'noSymbols' => true ),
		'shipToCompany'				=> array( 'maxLength' => 50, 'noSymbols' => true ),
		'shipToCountry'				=> array( 'maxLength' => 60, 'noSymbols' => true ),
		'shipToFaxNumber'			=> array( 'maxLength' => 25, 'charMask' => '\d\(\)\-\.' ),
		'shipToFirstName'			=> array( 'maxLength' => 50, 'noSymbols' => true ),
		'shipToLastName'			=> array( 'maxLength' => 50, 'noSymbols' => true ),
		'shipToPhoneNumber'			=> array( 'maxLength' => 25, 'charMask' => '\d\(\)\-\.' ),
		'shipToState'				=> array( 'maxLength' => 40, 'noSymbols' => true ),
		'shipToZip'					=> array( 'maxLength' => 20, 'noSymbols' => true ),
		'splitTenderId'				=> array( 'maxLength' => 6 ),
		'taxAmount'					=> array(  ),
		'taxDescription'			=> array( 'maxLength' => 255 ),
		'taxExempt'					=> array( 'enum' => array( 'true', 'false' ) ),
		'taxName'					=> array( 'maxLength' => 31 ),
		'transactionKey'			=> array( 'maxLength' => 16, 'noSymbols' => true ),
		'transactionType'			=> array(
			'enum' => array(
				// Old types
				'profileTransAuthCapture',
				'profileTransAuthOnly',
				'profileTransCaptureOnly',
				'profileTransPriorAuthCapture',
				'profileTransRefund',
				'profileTransVoid',
				// New types
				'authCaptureTransaction',
				'authOnlyTransaction',
				'captureOnlyTransaction',
				'priorAuthCaptureTransaction',
				'refundTransaction',
				'voidTransaction',
			),
		),
		'transId'					=> array( 'charMask' => '\d' ),
		'validationMode'			=> array( 'enum' => array( 'liveMode', 'testMode', 'none' ) ),
	);

	protected $_txnTypeMap		= array(
		'authCaptureTransaction'      => 'auth_capture',
		'authOnlyTransaction'         => 'auth_only',
		'captureOnlyTransaction'      => 'capture_only',
		'priorAuthCaptureTransaction' => 'prior_auth_capture',
		'refundTransaction'           => 'credit',
		'voidTransaction'             => 'void',
	);
	
	/**
	 * Set the API credentials so they go through validation.
	 */
	public function clearParameters()
	{
		parent::clearParameters();
		
		if( isset( $this->_defaults['login'] ) && isset( $this->_defaults['password'] ) ) {
			$this->setParameter( 'loginId', $this->_defaults['login'] );
			$this->setParameter( 'transactionKey', $this->_defaults['password'] );
		}
		
		return $this;
	}
	
	/**
	 * Send the given request to Authorize.Net and process the results.
	 */
	protected function _runTransaction( $request, $params )
	{
		$auth = array(
			'@attributes'				=> array(
				'xmlns'						=> 'AnetApi/xml/v1/schema/AnetApiSchema.xsd',
			),
			'merchantAuthentication'	=> array(
				'name'						=> $this->getParameter('loginId'),
				'transactionKey'			=> $this->getParameter('transactionKey'),
			)
		);
		
		$xml = $this->_arrayToXml( $request, $auth + $params );
		
		$this->_lastRequest = $xml;
		
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $this->_endpoint );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml") );
		curl_setopt( $curl, CURLOPT_HEADER, 0 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $xml );
		
		if( !in_array( $request, array( 'createTransactionRequest', 'createCustomerProfileTransactionRequest' ) ) ) {
			curl_setopt( $curl, CURLOPT_TIMEOUT, 15 );
		}
		
		curl_setopt( $curl, CURLOPT_CAINFO, Mage::getModuleDir( '', 'ParadoxLabs_AuthorizeNetCim' ) . '/resources/authorizenet-cert.pem' );
		
		if( $this->_verifySsl === true ) {
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
		}
		else {
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
		}
		
		$this->_lastResponse = curl_exec( $curl );
		
		if( $this->_lastResponse && !curl_errno( $curl ) ) {
			$this->_log .= 'REQUEST: ' . $this->_sanitizeLog( $xml ) . "\n";
			$this->_log .= 'RESPONSE: ' . $this->_sanitizeLog( $this->_lastResponse ) . "\n";
			
			$this->_lastResponse = $this->_xmlToArray( $this->_lastResponse );

			if( $this->_testMode === true ) {
				Mage::helper('tokenbase')->log( $this->_code . '-debug', $this->_log, true );
			}
			
			/**
			 * Check for basic errors.
			 */
			if( $this->_lastResponse['messages']['resultCode'] != 'Ok' ) {
				$errorCode		= $this->_getArrayValue( $this->_lastResponse, 'messages/message/code' );
				$errorText		= $this->_getArrayValue( $this->_lastResponse, 'messages/message/text' );
				$errorText2		= $this->_getArrayValue( $this->_lastResponse, 'transactionResponse/errors/error/errorText' );
				
				/**
				 * Log and spit out generic error. Skip certain warnings we can handle.
				 */
				$okayErrorCodes	= array( 'E00039', 'E00040' );
				$okayErrorTexts	= array( 'The referenced transaction does not meet the criteria for issuing a credit.', 'The transaction cannot be found.' );
				
				if( !empty($errorCode) && !in_array( $errorCode, $okayErrorCodes ) && !in_array( $errorText, $okayErrorTexts ) && !in_array( $errorText2, $okayErrorTexts ) ) {
					Mage::helper('tokenbase')->log( $this->_code, sprintf( "API error: %s: %s\n%s", $errorCode, $errorText, $this->_log ) );
					throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__( sprintf( 'Authorize.Net CIM Gateway: %s (%s)', $errorText, $errorCode ) ) );
				}
			}
			
			curl_close($curl);
		}
		else {
			Mage::helper('tokenbase')->log( $this->_code, sprintf( 'CURL Connection error: ' . curl_error($curl) . ' (' . curl_errno($curl) . ')' . "\n" . 'REQUEST: ' . $this->_sanitizeLog( $xml ) ) );
			Mage::throwException( Mage::helper('tokenbase')->__( sprintf( 'Authorize.Net CIM Gateway Connection error: %s (%s)', curl_error($curl), curl_errno($curl) ) ) );
		}
		
		return $this->_lastResponse;
	}
	
	/**
	 * Mask certain values in the XML for secure logging purposes.
	 */
	protected function _sanitizeLog( $string )
	{
		$maskAll	= array( 'cardCode' );
		$maskFour	= array( 'cardNumber', 'name', 'transactionKey', 'routingNumber', 'accountNumber' );
		
		foreach( $maskAll as $val ) {
			$string	= preg_replace( '#' . $val . '>(.+?)</' . $val . '#', $val . '>XXX</' . $val, $string );
		}
		
		foreach( $maskFour as $val ) {
			$start	= strpos( $string, '<' . $val . '>' );
			$end	= strpos( $string, '</' . $val . '>', $start );
			$tagLen	= strlen( $val ) + 2;
			
			if( $start !== false && $end > ( $start + $tagLen + 4 ) ) {
				$string = substr_replace( $string, 'XXXX', $start + $tagLen, $end - 4 - ($start + $tagLen) );
			}
		}
		
		return $string;
	}
	
	/**
	 * Convert XML string to array. See tokenbase/gateway_xml
	 */
	protected function _xmlToArray( $xml )
	{
		// Strip bad namespace out before we try to parse it. ...
		$xml = str_replace( ' xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"', '', $xml );
		
		return parent::_xmlToArray( $xml );
	}
	
	/**
	 * Turn transaction results and directResponse into a usable object.
	 */
	protected function _interpretTransaction( $transactionResult )
	{
		/**
		 * Check for not-found error first. If that error makes it here, that means they attempted to use a stored card
		 * that could not be found (deleted, or account change, or such). Any way about it the card is no longer valid.
		 */
		if( $transactionResult['messages']['resultCode'] != 'Ok' ) {
			$errorCode		= $transactionResult['messages']['message']['code'];
			$errorText		= $transactionResult['messages']['message']['text'];
			
			if( $errorCode == 'E00040' && $errorText == 'Customer Profile ID or Customer Payment Profile ID not found.' ) {
				if( $this->getCard() ) {
					/**
					 * We know the card is not valid, so hide and get rid of it.
					 * Except we're in the middle of a transaction... so any change will just be rolled back. Save it for a little later.
					 * @see ParadoxLabs_TokenBase_Model_Observer_CardLoad::checkQueuedForDeletion()
					 */
					Mage::unregister('queue_card_deletion');
					Mage::register( 'queue_card_deletion', $this->getCard() );
				}
				
				Mage::helper('tokenbase')->log( $this->_code, sprintf( "API error: %s: %s\n%s", $errorCode, $errorText, $this->_log ) );
				throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__( sprintf( 'Sorry, we were unable to find your payment record. Please re-enter your payment info and try again.' ) ) );
			}
			elseif( $errorCode == 'E00040' && $errorText == 'Customer Shipping Address ID not found.' ) {
				/**
				 * Invalid shipping ID. We should retry, but that's hard to do with this architecture. In a transaction, no events, ...
				 */
				Mage::helper('tokenbase')->log( $this->_code, sprintf( "API error: %s: %s\n%s", $errorCode, $errorText, $this->_log ) );
				Mage::throwException( Mage::helper('tokenbase')->__( sprintf( 'Authorize.Net CIM Gateway: %s Please contact support, or delete your shipping address in My Account and try again.', $errorText ) ) );
			}
		}
		
		/**
		 * Turn response into a consistent data object, as best we can
		 */
		if (isset($transactionResult['directResponse'])) {
			$data = $this->_getDataFromDirectResponse( $transactionResult['directResponse'] );
		}
		elseif (isset($transactionResult['transactionResponse'])) {
			$data = $this->_getDataFromTransactionResponse( $transactionResult['transactionResponse'] );
		}
		else {
			Mage::helper('tokenbase')->log( $this->_code, sprintf( "Authorize.Net CIM Gateway: Transaction failed; no response.\n%s", $this->_log ) );
			throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__( 'Authorize.Net CIM Gateway: Transaction failed; no response. Please re-enter your payment info and try again.' ) );
		}
		
		$response = new Varien_Object();
		$response->setData( $data );
		
		if( $response->getResponseCode() == 4 ) {
			$response->setIsFraud( true );
		}
		
		if( !in_array( $response->getResponseReasonCode() , array( 16, 54 ) ) ) { // Response 54 is 'can't refund; txn has not settled.' 16 is 'cannot find txn' (expired). We deal with them.
			if( $transactionResult['messages']['resultCode'] != 'Ok' 							// Error result
				|| in_array( $response->getResponseCode(), array( 2, 3 ) )						// OR error/decline response code
				|| ( !in_array( $response->getTransactionType(), array( 'credit', 'void' ) )	// OR no transID or auth code on a charge txn
					&& ( $response->getTransactionId() == '' || ( $response->getAuthCode() == '' && $response->getMethod() != 'ECHECK' ) ) ) ) {
				$response->setIsError( true );
				
				Mage::helper('tokenbase')->log( $this->_code, sprintf( "Transaction error: %s\n%s\n%s", $response->getResponseReasonText(), json_encode( $response->getData() ), $this->_log ) );
				throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__( 'Authorize.Net CIM Gateway: Transaction failed. ' . $response->getResponseReasonText() ) );
			}
		}
		
		return $response;
	}

	/**
	 * Turn the direct response string into an array, as best we can.
	 *
	 * @param string $directResponse
	 * @return array
	 */
	protected function _getDataFromDirectResponse( $directResponse )
	{
		if( strlen($directResponse) > 1 ) {
			// Strip out quotes, we don't want any.
			$directResponse = str_replace( '"', '', $directResponse );

			// Use the second character as the delimiter. The first will always be the one-digit response code.
			$directResponse = explode( substr( $directResponse, 1, 1 ), $directResponse );
		}

		if( empty($directResponse) || count($directResponse) == 0 ) {
			Mage::helper('tokenbase')->log( $this->_code, sprintf( "Authorize.Net CIM Gateway: Transaction failed; no direct response.\n%s", $this->_log ) );
			throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Authorize.Net CIM Gateway: Transaction failed; no direct response. Please re-enter your payment info and try again.') );
		}

		/**
		 * Turn the array into a keyed object and infer some things.
		 */
		$data = array(
			'response_code'           => (int)$directResponse[0],
			'response_subcode'        => (int)$directResponse[1],
			'response_reason_code'    => (int)$directResponse[2],
			'response_reason_text'    => $directResponse[3],
			'approval_code'           => $directResponse[4],
			'auth_code'               => $directResponse[4],
			'avs_result_code'         => $directResponse[5],
			'transaction_id'          => $directResponse[6],
			'invoice_number'          => $directResponse[7],
			'description'             => $directResponse[8],
			'amount'                  => $directResponse[9],
			'method'                  => $directResponse[10],
			'transaction_type'        => $directResponse[11],
			'customer_id'             => $directResponse[12],
			'md5_hash'                => $directResponse[37],
			'card_code_response_code' => $directResponse[38],
			'cavv_response_code'      => $directResponse[39],
			'acc_number'              => $directResponse[50],
			'card_type'               => $directResponse[51],
			'split_tender_id'         => $directResponse[52],
			'requested_amount'        => $directResponse[53],
			'balance_on_card'         => $directResponse[54],
			'profile_id'              => $this->getParameter('customerProfileId'),
			'payment_id'              => $this->getParameter('customerPaymentProfileId'),
			'is_fraud'                => false,
			'is_error'                => false,
		);

		return $data;
	}

	/**
	 * Turn the transaction response into an array, as best we can.
	 *
	 * @param array $response
	 * @return array
	 */
	protected function _getDataFromTransactionResponse( $response )
	{
		if( empty($response) ) {
			Mage::helper('tokenbase')->log( $this->_code, sprintf( "Authorize.Net CIM Gateway: Transaction failed; no response.\n%s", $this->_log ) );
			throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Authorize.Net CIM Gateway: Transaction failed; no response. Please re-enter your payment info and try again.') );
		}

		/**
		 * Turn the array into a keyed object and infer some things.
		 * We try to keep the values consistent with the directResponse data. Some translation required.
		 */
		$data = array(
			'response_code'            => (int)$this->_getArrayValue( $response, 'responseCode' ),
			'response_subcode'         => '',
			'response_reason_code'     => (int)$this->_getArrayValue( $response, 'errors/error/errorCode' ),
			'response_reason_text'     => $this->_getArrayValue( $response, 'errors/error/errorText' ),
			'approval_code'            => $this->_getArrayValue( $response, 'authCode' ),
			'auth_code'                => $this->_getArrayValue( $response, 'authCode' ),
			'avs_result_code'          => $this->_getArrayValue( $response, 'avsResultCode' ),
			'transaction_id'           => $this->_getArrayValue( $response, 'transId' ),
			'reference_transaction_id' => $this->_getArrayValue( $response, 'refTransId' ),
			'invoice_number'           => $this->getParameter('invoiceNumber'),
			'description'              => $this->getParameter('description'),
			'amount'                   => $this->getParameter('amount'),
			'method'                   => ($this->_getArrayValue( $response, 'accountType' ) == 'eCheck') ? 'ECHECK' : 'CC',
			'transaction_type'         => $this->_txnTypeMap[ $this->getParameter('transactionType') ],
			'customer_id'              => $this->getParameter('merchantCustomerId'),
			'md5_hash'                 => $this->_getArrayValue( $response, 'transHash' ),
			'card_code_response_code'  => $this->_getArrayValue( $response, 'cvvResultCode' ),
			'cavv_response_code'       => $this->_getArrayValue( $response, 'cavvResultCode' ),
			'acc_number'               => $this->_getArrayValue( $response, 'accountNumber' ),
			'card_type'                => $this->_getArrayValue( $response, 'accountType' ),
			'split_tender_id'          => '',
			'requested_amount'         => '',
			'balance_on_card'          => '',
			'profile_id'               => $this->getParameter('customerProfileId'),
			'payment_id'               => $this->getParameter('customerPaymentProfileId'),
			'is_fraud'                 => false,
			'is_error'                 => false,
		);

		/**
		 * Pull CIM profile data out of the response, if any.
		 */
		if( isset( $response['profileResponse'] ) ) {
			$data['profile_results'] = $this->_getArrayValue( $response, 'profileResponse/messages' );

			if( $this->_getArrayValue( $response, 'profileResponse/customerProfileId', false ) !== false ) {
				$data['profile_id'] = $this->_getArrayValue( $response, 'profileResponse/customerProfileId' );
			}

			if( $this->_getArrayValue( $response, 'profileResponse/customerPaymentProfileIdList/numericString', false ) !== false ) {
				$data['payment_id'] = $this->_getArrayValue( $response, 'profileResponse/customerPaymentProfileIdList/numericString' );
			}

			if( $this->_getArrayValue( $response, 'profileResponse/customerShippingAddressIdList/numericString', false ) !== false ) {
				$data['shipping_id'] = $this->_getArrayValue( $response, 'profileResponse/customerShippingAddressIdList/numericString' );
			}
		}

		return $data;
	}
	
	/**
	 * Force a consistent data interface for the transaction data store.
	 * The data returned by API call getTransactionDetails does not match _getDataFromTransactionResponse.
	 *
	 * @param  array $response Results of API call getTransactionDetails
	 * @return array Data keyed to match normal transaction responses.
	 */
	protected function _mapTransactionDetails( $response )
	{
		if( empty($response) ) {
			Mage::helper('tokenbase')->log( $this->_code, sprintf( "Authorize.Net CIM Gateway: Transaction failed; no response.\n%s", $this->_log ) );
			throw Mage::exception( 'Mage_Payment_Model_Info', Mage::helper('tokenbase')->__('Authorize.Net CIM Gateway: Transaction failed; no response.') );
		}
		
		$txn	= $response['transaction'];
		$eCheck	= $this->_getArrayValue( $txn, 'payment/bankAccount', false ) !== false ? true : false;
		
		// Map data.
		$data	= array(
			'response_code'            => (int)$this->_getArrayValue( $txn, 'responseCode' ),
			'response_reason_code'     => (int)$this->_getArrayValue( $txn, 'responseReasonCode' ),
			'response_reason_text'     => $this->_getArrayValue( $txn, 'responseReasonDescription' ),
			'transaction_status'       => $this->_getArrayValue( $txn, 'transactionStatus' ),
			'approval_code'            => $this->_getArrayValue( $txn, 'authCode' ),
			'auth_code'                => $this->_getArrayValue( $txn, 'authCode' ),
			'avs_result_code'          => $this->_getArrayValue( $txn, 'AVSResponse' ),
			'transaction_id'           => $this->_getArrayValue( $txn, 'transId' ),
			'reference_transaction_id' => $this->_getArrayValue( $txn, 'refTransId' ),
			'invoice_number'           => $this->_getArrayValue( $txn, 'order/invoiceNumber' ),
			'description'              => $this->_getArrayValue( $txn, 'order/description' ),
			'amount'                   => $this->_getArrayValue( $txn, 'authAmount' ),
			'method'                   => $eCheck ? 'ECHECK' : 'CC',
			'transaction_type'         => $this->_getArrayValue( $this->_txnTypeMap, $this->_getArrayValue( $txn, 'transactionType' ) ),
			'customer_id'              => $this->_getArrayValue( $txn, 'customer/id' ),
			'card_code_response_code'  => $this->_getArrayValue( $txn, 'cardCodeResponse' ),
			'cavv_response_code'       => $this->_getArrayValue( $txn, 'CAVVResponse' ),
			'acc_number'               => $this->_getArrayValue( $txn, $eCheck ? 'payment/bankAccount/accountNumber' : 'payment/creditCard/cardNumber' ),
			'card_type'                => $this->_getArrayValue( $txn, $eCheck ? 'payment/bankAccount/echeckType' : 'payment/creditCard/accountType' ),
			'submit_time_utc'          => $this->_getArrayValue( $txn, 'submitTimeUTC' ),
			'amount_settled'           => $this->_getArrayValue( $txn, 'settleAmount' ),
			'amount_tax'               => $this->_getArrayValue( $txn, 'tax/amount' ),
			'amount_shipping'          => $this->_getArrayValue( $txn, 'shipping/amount' ),
			'amount_duty'              => $this->_getArrayValue( $txn, 'duty/amount' ),
			'line_items'               => $this->_getArrayValue( $txn, 'lineItems/lineItem/itemId', false ) !== false ? array( 0 => $this->_getArrayValue( $txn, 'lineItems/lineItem' ) ) : $this->_getArrayValue( $txn, 'lineItems/lineItem' ),
			'tax_exempt'               => $this->_getArrayValue( $txn, 'taxExempt' ),
			'expiration_date'          => $this->_getArrayValue( $txn, 'payment/creditCard/expirationDate' ),
			'customer_email'           => $this->_getArrayValue( $txn, 'customer/email' ),
			'customer_ip'              => $this->_getArrayValue( $txn, 'customerIP' ),
			'batch_id'                 => $this->_getArrayValue( $txn, 'batch/batchId' ),
			'settlement_time_utc'      => $this->_getArrayValue( $txn, 'batch/settlementTimeUTC' ),
			'settlement_state'         => $this->_getArrayValue( $txn, 'batch/settlementState' ),
			'fraud_filter_action'      => $this->_getArrayValue( $txn, 'FDSFilterAction' ),
			'fraud_filter'             => $this->_getArrayValue( $txn, 'FDSFilters' ),
		);
		
		// Clean out empties.
		foreach( $data as $key => $value ) {
			if( $value === '' ) {
				unset( $data[ $key ] );
			}
		}
		
		return $data;
	}
	
	/**
	 * Magento-exposed actions
	 */
	public function setCard( ParadoxLabs_TokenBase_Model_Card $card )
	{
		$this->setParameter( 'email', $card->getCustomerEmail() );
		$this->setParameter( 'merchantCustomerId', $card->getCustomerId() );
		$this->setParameter( 'customerProfileId', $card->getProfileId() );
		$this->setParameter( 'customerPaymentProfileId', $card->getPaymentId() );
		$this->setParameter( 'customerIp', $card->getCustomerIp() );
		
		return parent::setCard( $card );
	}
	
	public function authorize( $payment, $amount )
	{
		$this->setParameter( 'transactionType', 'authOnlyTransaction' );
		$this->setParameter( 'amount', $amount );
		$this->setParameter( 'invoiceNumber', $payment->getOrder()->getIncrementId() );
		
		if( $this->getIsReauthorize() !== true ) {
			if( $payment->getOrder()->getBaseTaxAmount() ) {
				$this->setParameter( 'taxAmount', $payment->getOrder()->getBaseTaxAmount() );
			}
			
			if( $payment->getBaseShippingAmount() ) {
				$this->setParameter( 'shipAmount', $payment->getBaseShippingAmount() );
			}
		}
		
		if( $payment->hasCcCid() && $payment->getCcCid() != '' ) {
			$this->setParameter( 'cardCode', $payment->getCcCid() );
		}
		
		$result		= $this->createTransaction();
		$response	= $this->_interpretTransaction( $result );
		
		return $response;
	}
	
	public function capture( $payment, $amount, $realTransactionId=null )
	{
		if( $this->getHaveAuthorized() ) {
			$this->setParameter( 'transactionType', 'priorAuthCaptureTransaction' );
			
			if( !is_null( $realTransactionId ) ) {
				$this->setParameter( 'transId', $realTransactionId );
			}
			elseif( $this->hasTransactionId() ) {
				$this->setParameter( 'transId', $this->getTransactionId() );
			}
		}
		else {
			$this->setParameter( 'transactionType', 'authCaptureTransaction' );
		}
		
		$this->setParameter( 'amount', $amount );
		$this->setParameter( 'invoiceNumber', $payment->getOrder()->getIncrementId() );
		
		if( $this->hasAuthCode() ) {
			$this->setParameter( 'approvalCode', $this->getAuthCode() );
		}
		
		// Grab shipping and tax info from the invoice if possible. Should always be true.
		if( $payment->hasInvoice() && $payment->getInvoice() instanceof Mage_Sales_Model_Order_Invoice ) {
			if( $payment->getInvoice()->getBaseTaxAmount() ) {
				$this->setParameter( 'taxAmount', $payment->getInvoice()->getBaseTaxAmount() );
			}
			
			if( $payment->getInvoice()->getBaseShippingAmount() ) {
				$this->setParameter( 'shipAmount', $payment->getInvoice()->getBaseShippingAmount() );
			}
		}
		elseif( $payment->getOrder()->getBaseTotalPaid() <= 0 ) {
			if( $payment->getOrder()->getBaseTaxAmount() ) {
				$this->setParameter( 'taxAmount', $payment->getOrder()->getBaseTaxAmount() );
			}
			
			if( $payment->getBaseShippingAmount() ) {
				$this->setParameter( 'shipAmount', $payment->getBaseShippingAmount() );
			}
		}
		
		if( $payment->hasCcCid() && $payment->getCcCid() != '' ) {
			$this->setParameter( 'cardCode', $payment->getCcCid() );
		}
		
		$result		= $this->createTransaction();
		$response	= $this->_interpretTransaction( $result );
		
		/**
		 * Check for and handle 'transaction not found' error (expired authorization).
		 */
		if( $response->getResponseReasonCode() == 16 && $this->getParameter('transId') != '' ) {
			Mage::helper('tokenbase')->log( $this->_code, sprintf( "Transaction not found. Attempting to recapture.\n%s", json_encode( $response->getData() ) ) );
			
			$this->unsAuthCode()
				 ->unsHaveAuthorized()
				 ->clearParameters()
				 ->setCard( $this->getCard() );
			
			$response = $this->capture( $payment, $amount, '' );
		}
		
		return $response;
	}
	
	public function refund( $payment, $amount, $realTransactionId=null )
	{
		$this->setParameter( 'transactionType', 'refundTransaction' );
		$this->setParameter( 'amount', $amount );
		$this->setParameter( 'invoiceNumber', $payment->getOrder()->getIncrementId() );
		
		if( $payment->getCreditmemo()->getBaseTaxAmount() ) {
			$this->setParameter( 'taxAmount', $payment->getCreditmemo()->getBaseTaxAmount() );
		}
		
		if( $payment->getCreditmemo()->getBaseShippingAmount() ) {
			$this->setParameter( 'shipAmount', $payment->getCreditmemo()->getBaseShippingAmount() );
		}
		
		if( !is_null( $realTransactionId ) ) {
			$this->setParameter( 'transId', $realTransactionId );
		}
		elseif( $this->hasTransactionId() ) {
			$this->setParameter( 'transId', $this->getTransactionId() );
		}
		
		$result		= $this->createTransaction();
		$response	= $this->_interpretTransaction( $result );
		
		/**
		 * Check for 'transaction unsettled' error.
		 */
		if( $response->getResponseReasonCode() == 54 ) {
			/**
			 * Is this a full refund? If so, just void it. Nobody will see the difference.
			 */
			if( $amount == $payment->getCreditmemo()->getInvoice()->getBaseGrandTotal() ) {
				$transactionId = $this->getParameter('transId');
				
				return $this->clearParameters()->setCard( $this->getCard() )->void( $payment, $transactionId );
			}
			else {
				$response->setIsError( true );
				
				Mage::helper('tokenbase')->log( $this->_code, sprintf( "Transaction error: %s\n%s\n%s", $response->getResponseReasonText(), json_encode( $response->getData() ), $this->_log ) );
				Mage::throwException( Mage::helper('tokenbase')->__( 'Authorize.Net CIM Gateway: Transaction failed. ' . $response->getResponseReasonText() ) );
			}
		}
		
		return $response;
	}
	
	public function void( $payment, $realTransactionId=null )
	{
		$this->setParameter( 'transactionType', 'voidTransaction' );
		
		if( !is_null( $realTransactionId ) ) {
			$this->setParameter( 'transId', $realTransactionId );
		}
		elseif( $this->hasTransactionId() ) {
			$this->setParameter( 'transId', $this->getTransactionId() );
		}
		
		$result		= $this->createTransaction();
		$response	= $this->_interpretTransaction( $result );
		
		return $response;
	}
	
	public function fraudUpdate( $payment, $transactionId )
	{
		$this->setParameter( 'transId', $transactionId );
		
		$result		= $this->getTransactionDetails();
		
		foreach( $result as $k => $v ) {
			if( is_array( $v ) ) {
				foreach( $v as $l => $u ) {
					if( is_array( $u ) ) {
						$u = json_encode( $u );
					}
					
					$result[ $k . '_' . $l ] = $u;
					
					unset( $result[ $k ][ $l ] );
				}
				
				if( empty( $result[ $k ] ) ) {
					unset( $result[ $k ] );
				}
			}
		}
		
		$response	= new Varien_Object( $result + array( 'is_approved' => false, 'is_denied' => false ) );
		
		if( $response->getResponseReasonCode() == 254 || $response->getTransactionStatus() == 'voided' ) { // Transaction pending review -> denied
			$response->setIsDenied( true );
		}
		elseif( $response->getResponseCode() == 1 ) {
			$response->setIsApproved( true );
		}
		
		return $response;
	}
	
	/**
	 * API methods: See the Authorize.Net CIM XML documentation.
	 */
	public function createCustomerProfile()
	{
		$params = array(
			'profile'					=> array(
				'merchantCustomerId'		=> intval( $this->getParameter('merchantCustomerId') ),
				'description'				=> $this->getParameter('description'),
				'email'						=> $this->getParameter('email'),
			),
		);
		
		$result = $this->_runTransaction( 'createCustomerProfileRequest', $params );
		
		$text	= $this->_getArrayValue( $result, 'messages/message/text' );
		
		if( isset( $result['customerProfileId'] ) ) {
			return $result['customerProfileId'];
		}
		elseif( strpos( $text, 'duplicate' ) !== false ) {
			return preg_replace( '/[^0-9]/', '', $text );
		}
		else {
			$this->logLogs();
			
			Mage::throwException( Mage::helper('tokenbase')->__( 'Authorize.Net CIM Gateway: Unable to create customer profile. %s', $result['messages']['message']['text'] ) );
		}
	}
	
	public function createCustomerPaymentProfile()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
			'paymentProfile'			=> array(
				'billTo'					=> array(
					'firstName'					=> $this->getParameter('billToFirstName'),
					'lastName'					=> $this->getParameter('billToLastName'),
					'company'					=> $this->getParameter('billToCompany'),
					'address'					=> $this->getParameter('billToAddress'),
					'city'						=> $this->getParameter('billToCity'),
					'state'						=> $this->getParameter('billToState'),
					'zip'						=> $this->getParameter('billToZip'),
					'country'					=> $this->getParameter('billToCountry'),
					'phoneNumber'				=> $this->getParameter('billToPhoneNumber'),
					'faxNumber'					=> $this->getParameter('billToFaxNumber'),
				),
				'payment'					=> array(),
			),
			'validationMode'			=> $this->getParameter('validationMode'),
		);
		
		if( $this->hasParameter('customerType') ) {
			$params['paymentProfile'] = array(
				'customerType'				=> $this->getParameter('customerType')
			) + $params['paymentProfile'];
		}
		
		if( $this->hasParameter('cardNumber') ) {
			$params['paymentProfile']['payment'] = array(
				'creditCard'				=> array(
					'cardNumber'				=> $this->getParameter('cardNumber'),
					'expirationDate'			=> $this->getParameter('expirationDate'),
				),
			);
			
			if( $this->hasParameter('cardCode') ) {
				$params['paymentProfile']['payment']['creditCard']['cardCode'] = $this->getParameter('cardCode');
			}
		}
		elseif( $this->hasParameter('accountNumber') ) {
			$params['paymentProfile']['payment'] = array(
				'bankAccount'				=> array(
					'accountType'				=> $this->getParameter('accountType'),
					'routingNumber'				=> $this->getParameter('routingNumber'),
					'accountNumber'				=> $this->getParameter('accountNumber'),
					'nameOnAccount'				=> $this->getParameter('nameOnAccount'),
					'echeckType'				=> $this->getParameter('echeckType'),
					'bankName'					=> $this->getParameter('bankName'),
				),
			);
		}
		
		$result = $this->_runTransaction( 'createCustomerPaymentProfileRequest', $params );
		
		$paymentId = null;
		
		if( isset( $result['customerPaymentProfileId'] ) ) {
			$paymentId = $result['customerPaymentProfileId'];
		}
		
		$text	= $this->_getArrayValue( $result, 'messages/message/text' );
		
		if( strpos( $text, 'duplicate' ) !== false ) {
			/**
			 * Handle duplicate card errors. Painful process.
			 */
			
			if( empty( $paymentId ) ) {
				$paymentId = preg_replace( '/[^0-9]/', '', $text );
			}
			
			/**
			 * If we still have no payment ID, try to match the duplicate manually.
			 * Authorize.Net does not return the ID in this duplicate error message, contrary to documentation.
			 */
			if( empty( $paymentId ) ) {
				$paymentId = $this->findDuplicateCard();
			}
			
			if( !empty( $paymentId ) ) {
				// Update the card record to ensure CVV and expiry are up to date.
				$this->setParameter( 'customerPaymentProfileId', $paymentId );
				$this->updateCustomerPaymentProfile();
			}
		}
		
		return $paymentId;
	}
	
	public function createCustomerShippingAddress()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
			'address'					=> array(
				'firstName'					=> $this->getParameter('shipToFirstName'),
				'lastName'					=> $this->getParameter('shipToLastName'),
				'company'					=> $this->getParameter('shipToCompany'),
				'address'					=> $this->getParameter('shipToAddress'),
				'city'						=> $this->getParameter('shipToCity'),
				'state'						=> $this->getParameter('shipToState'),
				'zip'						=> $this->getParameter('shipToZip'),
				'country'					=> $this->getParameter('shipToCountry'),
				'phoneNumber'				=> $this->getParameter('shipToPhoneNumber'),
				'faxNumber'					=> $this->getParameter('shipToFaxNumber'),
			),
		);
		
		$result = $this->_runTransaction( 'createCustomerShippingAddressRequest', $params );
		
		$text 	= $this->_getArrayValue( $result, 'messages/message/text' );
		
		if( isset( $result['customerAddressId'] ) ) {
			return $result['customerAddressId'];
		}
		elseif( strpos( $text, 'duplicate' ) !== false ) {
			/**
			 * Handle duplicate address errors. blah.
			 */
			$profile	= $this->getCustomerProfile();
			
			if( isset( $profile['profile']['shipToList'] ) && count( $profile['profile']['shipToList'] ) > 0 ) {
				if( isset( $profile['profile']['shipToList']['customerAddressId'] ) ) {
					return $profile['profile']['shipToList']['customerAddressId'];
				}
				else {
					foreach( $profile['profile']['shipToList'] as $address ) {
						$isDuplicate	= true;
						$fields			= array( 'firstName', 'lastName', 'address', 'zip', 'phoneNumber' );
						
						foreach( $fields as $field ) {
							if( $address[ $field ] != $params['address'][ $field ] ) {
								$isDuplicate = false;
								break;
							}
						}
						
						if( $isDuplicate === true ) {
							return $address['customerAddressId'];
						}
					}
				}
			}
		}
		else {
			$this->logLogs();
			
			Mage::throwException( Mage::helper('tokenbase')->__( 'Authorize.Net CIM Gateway: Unable to create shipping address record.' ) );
		}
	}
	
	/**
	 * Find a duplicate CIM record matching the one we just tried to create.
	 */
	public function findDuplicateCard()
	{
		$profile	= $this->getCustomerProfile();
		$lastFour	= substr( $this->getParameter('cardNumber'), -4 );
		
		if( isset( $profile['profile']['paymentProfiles'] ) && count( $profile['profile']['paymentProfiles'] ) > 0 ) {
			// If there's only one, just stop. It has to be the match.
			if( isset( $profile['profile']['paymentProfiles']['billTo'] ) ) {
				$card = $profile['profile']['paymentProfiles'];
				return $card['customerPaymentProfileId'];
			}
			else {
				// Otherwise, compare end of the card number for each until one matches.
				foreach( $profile['profile']['paymentProfiles'] as $card ) {
					if( isset( $card['payment']['creditCard'] ) && $lastFour == substr( $card['payment']['creditCard']['cardNumber'], -4 ) ) {
						return $card['customerPaymentProfileId'];
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Run a CIM transaction with Authorize.Net with stored data.
	 *
	 * @return string Raw transaction result (XML)
	 * @deprecated since 2.2.4 - see createTransaction()
	 */
	public function createCustomerProfileTransaction()
	{
		$type = $this->getParameter('transactionType');
		
		$params = array(
			'transaction'				=> array(
				$type						=> array(
				),
			),
			'extraOptions'				=> array( '@cdata' => 'x_duplicate_window=15' ),
		);
		
		if( $this->hasParameter('amount') ) {
			$params['transaction'][ $type ]['amount'] = $this->formatAmount( $this->getParameter('amount') );
		}
		
		// Add customer IP?
		if( $this->hasParameter('customerIp') ) {
			$params['extraOptions']['@cdata'] .= '&x_customer_ip=' . $this->getParameter('customerIp');
		}
		
		// Add tax amount?
		if( $this->hasParameter('taxAmount') ) {
			$params['transaction'][ $type ]['tax'] = array(
				'amount'				=> $this->formatAmount( $this->getParameter('taxAmount') ),
				'name'					=> $this->getParameter('taxName'),
				'description'			=> $this->getParameter('taxDescription'),
			);
		}
		
		// Add shipping amount?
		if( $this->hasParameter('shipAmount') ) {
			$params['transaction'][ $type ]['shipping'] = array(
				'amount'				=> $this->formatAmount( $this->getParameter('shipAmount') ),
				'name'					=> $this->getParameter('shipName'),
				'description'			=> $this->getParameter('shipDescription'),
			);
		}
		
		// Add duty amount?
		if( $this->hasParameter('dutyAmount') ) {
			$params['transaction'][ $type ]['duty'] = array(
				'amount'				=> $this->formatAmount( $this->getParameter('dutyAmount') ),
				'name'					=> $this->getParameter('dutyName'),
				'description'			=> $this->getParameter('dutyDescription'),
			);
		}
		
		// Add line items?
		if( !is_null( $this->_lineItems ) && count( $this->_lineItems ) > 0 ) {
			$params['transaction'][ $type ]['lineItems'] = array();
			
			$count = 0;
			foreach( $this->_lineItems as $item ) {
				if( $item instanceof Varien_Object == false ) {
					continue;
				}
				
				if( $item->getQty() > 0 ) {
					$qty = $item->getQty();
				}
				else {
					$qty = $item->getQtyOrdered();
				}
				
				if( $qty <= 0 || $item->getPrice() <= 0 || $item->getSku() == '' ) {
					continue;
				}
				
				if( ++$count > 30 ) {
					break;
				}
				
				$params['transaction'][ $type ]['lineItems'][] = array(
					'itemId'				=> $this->setParameter( 'itemName', $item->getSku() )->getParameter('itemName'),
					'name'					=> $this->setParameter( 'itemName', $item->getName() )->getParameter('itemName'),
					'quantity'				=> $this->formatAmount( $qty ),
					'unitPrice'				=> $this->formatAmount( max( 0, $item->getPrice() - ( $item->getDiscountAmount() / $qty ) ) ),
				);
			}
			
			if( count( $params['transaction'][ $type ]['lineItems'] ) < 1 ) {
				unset( $params['transaction'][ $type ]['lineItems'] );
			}
		}
		
		$params['transaction'][ $type ]['customerProfileId']		= $this->getParameter('customerProfileId');
		$params['transaction'][ $type ]['customerPaymentProfileId']	= $this->getParameter('customerPaymentProfileId');
		
		// Various other optional or conditional fields
		if( $this->hasParameter('customerShippingAddressId') ) {
			$params['transaction'][ $type ]['customerShippingAddressId'] = $this->getParameter('customerShippingAddressId');
		}
		
		if( $this->hasParameter('invoiceNumber') && $type != 'profileTransPriorAuthCapture' ) {
			$params['transaction'][ $type ]['order'] = array(
				'invoiceNumber'			=> $this->getParameter('invoiceNumber'),
				'description'			=> $this->getParameter('description'),
				'purchaseOrderNumber'	=> $this->getParameter('purchaseOrderNumber'),
			);
		}
		
		if( $this->hasParameter('cardCode') && $type != 'profileTransPriorAuthCapture' ) {
			$params['transaction'][ $type ]['cardCode'] = $this->getParameter('cardCode');
		}
		
		if( $this->hasParameter('transId') && $type != 'profileTransAuthOnly' ) {
			$params['transaction'][ $type ]['transId'] = $this->getParameter('transId');
		}
		
		if( $this->hasParameter('splitTenderId') ) {
			$params['transaction'][ $type ]['splitTenderId'] = $this->getParameter('splitTenderId');
		}
		
		if( $this->hasParameter('approvalCode') && strlen( $this->getParameter('approvalCode') ) == 6 && !in_array( $type, array( 'profileTransRefund', 'profileTransPriorAuthCapture', 'profileTransAuthOnly' ) ) ) {
			$params['transaction'][ $type ]['approvalCode'] = $this->getParameter('approvalCode');
		}
		
		return $this->_runTransaction( 'createCustomerProfileTransactionRequest', $params );
	}

	/**
	 * Run an actual transaction with Authorize.Net with stored data.
	 *
	 * Implements the new generic API method (createTransactionRequest), as opposed
	 * to the CIM-specific implementation in createCustomerProfileTransaction().
	 *
	 * @return string Raw transaction result (XML)
	 */
	public function createTransaction()
	{
		$type = $this->getParameter('transactionType');

		if( in_array( $type, array('authOnlyTransaction', 'authCaptureTransaction', 'captureOnlyTransaction' ) ) ) {
			$isNewTxn = true;
		}
		else {
			$isNewTxn = false;
		}

		if( $this->hasParameter('customerProfileId') && $this->hasParameter('customerPaymentProfileId') ) {
			$isNewCard = false;
		}
		else {
			$isNewCard = true;
		}
		
		if( $type == 'refundTransaction' ) {
			$isRefund = true;
		}
		else {
			$isRefund = false;
		}

		/**
		 * Define the transaction and basics: Amount, txn ID, auth code
		 */
		$params = array();
		$params['transactionType'] = $type;

		if( $this->hasParameter('amount') ) {
			$params['amount'] = $this->formatAmount( $this->getParameter('amount') );
		}
		
		// profile must be above refTransId. Placeholder to enforce that.
		$params['profile'] = array();

		if( $isNewTxn === false ) {
			if( $this->hasParameter('transId') ) {
				$params['refTransId'] = $this->getParameter('transId');
			}

			if( $this->hasParameter('splitTenderId') ) {
				$params['splitTenderId'] = $this->getParameter('splitTenderId');
			}
		}

		if( $type == 'captureOnlyTransaction'
			&& $this->hasParameter('approvalCode')
			&& strlen($this->getParameter('approvalCode')) == 6 ) {
			$params['authCode'] = $this->getParameter('approvalCode');
		}

		// Most of the data does not matter for follow-ups (capture, void, refund).
		if( $isNewTxn === true || $isRefund === true ) {
			/**
			 * Add payment info.
			 */
			if( $isNewCard === true ) {
				/**
				 * If we're storing a new card, send the payment data along and request a profile.
				 */
				if( $this->hasParameter('cardNumber') ) {
					$params['payment'] = array(
						'creditCard' => array(
							'cardNumber'     => $this->getParameter('cardNumber'),
							'expirationDate' => $this->getParameter('expirationDate'),
						),
					);

					if( $this->hasParameter('cardCode') ) {
						$params['payment']['creditCard']['cardCode'] = $this->getParameter('cardCode');
					}
				}
				elseif( $this->hasParameter('accountNumber') ) {
					$params['payment'] = array(
						'bankAccount' => array(
							'accountType'   => $this->getParameter('accountType'),
							'routingNumber' => $this->getParameter('routingNumber'),
							'accountNumber' => $this->getParameter('accountNumber'),
							'nameOnAccount' => $this->getParameter('nameOnAccount'),
							'echeckType'    => $this->getParameter('echeckType'),
							'bankName'      => $this->getParameter('bankName'),
						),
					);
				}

				$params['profile']['createProfile'] = 'true';
			}
			elseif( $type != 'captureOnlyTransaction' ) {
				/**
				 * Otherwise, send the tokens we already have.
				 */
				$params['profile']['customerProfileId'] = $this->getParameter('customerProfileId');
				$params['profile']['paymentProfile']    = array(
					'paymentProfileId' => $this->getParameter('customerPaymentProfileId'),
				);

				// Include CCV if available.
				if( $this->hasParameter('cardCode') && $type != 'priorAuthCaptureTransaction' ) {
					$params['profile']['paymentProfile']['cardCode'] = $this->getParameter('cardCode');
				}

				// Include shipping profile if available.
				if( $this->hasParameter('customerShippingAddressId') ) {
					$params['profile']['shippingProfileId'] = $this->hasParameter('customerShippingAddressId');
				}
			}
			
			if( $isRefund !== true ) {
				// Set order identifiers!
				$params['solution'] = array(
					'id' => self::SOLUTION_ID,
				);
			}
			
			if( $this->hasParameter('invoiceNumber') && $type != 'priorAuthCaptureTransaction' ) {
				$params['order'] = array(
					'invoiceNumber' => $this->getParameter('invoiceNumber'),
					'description'   => $this->getParameter('description'),
				);
			}

			// Add line items?
			if( !is_null( $this->_lineItems ) && count( $this->_lineItems ) > 0 ) {
				$params['lineItems'] = array(
					'lineItem'  => array(),
				);

				$count = 0;
				foreach( $this->_lineItems as $item ) {
					if( ($item instanceof Varien_Object) == false) {
						continue;
					}

					if( $item->getQty() > 0 ) {
						$qty = $item->getQty();
					}
					else {
						$qty = $item->getQtyOrdered();
					}

					if( $qty <= 0 || $item->getPrice() <= 0 || $item->getSku() == '' ) {
						continue;
					}

					if( ++$count > 30 ) {
						break;
					}

					// Discount amount is per-line, not per-unit (???). Math it out.
					$unitPrice = max( 0, $item->getPrice() - ( $item->getDiscountAmount() / $qty ) );

					$params['lineItems']['lineItem'][] = array(
						// We're sending SKU and name through parameters to filter characters and length.
						'itemId'    => $this->setParameter( 'itemName', $item->getSku() )->getParameter('itemName'),
						'name'      => $this->setParameter( 'itemName', $item->getName() )->getParameter('itemName'),
						'quantity'  => $this->formatAmount( $qty ),
						'unitPrice' => $this->formatAmount( $unitPrice ),
					);
				}

				if( count( $params['lineItems']['lineItem'] ) < 1 ) {
					unset( $params['lineItems'] );
				}
			}

			// Add tax amount?
			if( $this->hasParameter('taxAmount') ) {
				$params['tax'] = array(
					'amount'      => $this->formatAmount( $this->getParameter('taxAmount') ),
					'name'        => $this->getParameter('taxName'),
					'description' => $this->getParameter('taxDescription'),
				);
			}

			// Add duty amount?
			if( $this->hasParameter('dutyAmount') ) {
				$params['duty'] = array(
					'amount'      => $this->formatAmount( $this->getParameter('dutyAmount') ),
					'name'        => $this->getParameter('dutyName'),
					'description' => $this->getParameter('dutyDescription'),
				);
			}

			// Add shipping amount?
			if( $this->hasParameter('shipAmount') ) {
				$params['shipping'] = array(
					'amount'      => $this->formatAmount( $this->getParameter('shipAmount') ),
					'name'        => $this->getParameter('shipName'),
					'description' => $this->getParameter('shipDescription'),
				);
			}

			// Add PO number?
			if( $this->hasParameter('purchaseOrderNumber') ) {
				$params['poNumber'] = $this->getParameter('purchaseOrderNumber');
			}

			// Add customer info!
			$params['customer'] = array(
				'id'    => $this->getParameter('merchantCustomerId'),
				'email' => $this->getParameter('email'),
			);

			if( $this->hasParameter('customerType') ) {
				$params['customer'] = array( 'type' => $this->getParameter('customerType') ) + $params['customer'];
			}

			// Add billing address?
			if( $isNewCard === true ) {
				$params['billTo'] = array(
					'firstName'   => $this->getParameter('billToFirstName'),
					'lastName'    => $this->getParameter('billToLastName'),
					'company'     => $this->getParameter('billToCompany'),
					'address'     => $this->getParameter('billToAddress'),
					'city'        => $this->getParameter('billToCity'),
					'state'       => $this->getParameter('billToState'),
					'zip'         => $this->getParameter('billToZip'),
					'country'     => $this->getParameter('billToCountry'),
					'phoneNumber' => $this->getParameter('billToPhoneNumber'),
					'faxNumber'   => $this->getParameter('billToFaxNumber'),
				);
			}

			// Add shipping address?
			if( !$this->hasParameter('customerShippingAddressId') && $this->hasParameter('shipToAddress') ) {
				$params['shipTo'] = array(
					'firstName' => $this->getParameter('shipToFirstName'),
					'lastName'  => $this->getParameter('shipToLastName'),
					'company'   => $this->getParameter('shipToCompany'),
					'address'   => $this->getParameter('shipToAddress'),
					'city'      => $this->getParameter('shipToCity'),
					'state'     => $this->getParameter('shipToState'),
					'zip'       => $this->getParameter('shipToZip'),
					'country'   => $this->getParameter('shipToCountry'),
				);
			}

			// Add customer IP?
			if( $this->hasParameter('customerIp') ) {
				$params['customerIP'] = $this->getParameter('customerIp');
			}

			// Add misc settings.
			$params['transactionSettings'] = array(
				'setting' => array(),
			);

			$params['transactionSettings']['setting'][] = array(
				'settingName'  => 'allowPartialAuth',
				'settingValue' => $this->getParameter('allowPartialAuth', 'false'),
			);

			$params['transactionSettings']['setting'][] = array(
				'settingName'  => 'duplicateWindow',
				'settingValue' => $this->getParameter('duplicateWindow', '15'),
			);

			$params['transactionSettings']['setting'][] = array(
				'settingName'  => 'emailCustomer',
				'settingValue' => $this->getParameter('emailCustomer', 'false'),
			);
		}
		
		if( empty( $params['profile'] ) ) {
			unset( $params['profile'] );
		}

		return $this->_runTransaction( 'createTransactionRequest', array( 'transactionRequest' => $params ) );
	}
	
	public function deleteCustomerProfile()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
		);
		
		return $this->_runTransaction( 'deleteCustomerProfileRequest', $params );
	}
	
	public function deleteCustomerPaymentProfile()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
			'customerPaymentProfileId'	=> $this->getParameter('customerPaymentProfileId'),
		);
		
		return $this->_runTransaction( 'deleteCustomerPaymentProfileRequest', $params );
	}
	
	public function deleteCustomerShippingAddress()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
			'customerShippingAddressId'	=> $this->getParameter('customerShippingAddressId'),
		);
		
		return $this->_runTransaction( 'deleteCustomerShippingAddressRequest', $params );
	}
	
	public function getCustomerProfileIds()
	{
		return $this->_runTransaction( 'getCustomerProfileIdsRequest', array() );
	}
	
	public function getCustomerProfile()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
		);
		
		return $this->_runTransaction( 'getCustomerProfileRequest', $params );
	}
	
	public function getCustomerPaymentProfile()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
			'customerPaymentProfileId'	=> $this->getParameter('customerPaymentProfileId'),
		);
		
		return $this->_runTransaction( 'getCustomerPaymentProfileRequest', $params );
	}
	
	public function getCustomerShippingAddress()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
			'customerShippingAddressId'	=> $this->getParameter('customerShippingAddressId'),
		);
		
		return $this->_runTransaction( 'getCustomerShippingAddressRequest', $params );
	}
	
	public function getTransactionDetails()
	{
		$params = array(
			'transId'					=> $this->getParameter('transId'),
		);
		
		$details = $this->_runTransaction( 'getTransactionDetailsRequest', $params );
		
		return $this->_mapTransactionDetails( $details );
	}
	
	public function updateCustomerProfile()
	{
		$params = array(
			'profile'					=> array(
				'merchantCustomerId'		=> $this->getParameter('merchantCustomerId'),
				'description'				=> $this->getParameter('description'),
				'email'						=> $this->getParameter('email'),
				'customerProfileId'			=> $this->getParameter('customerProfileId'),
			),
		);
		
		return $this->_runTransaction( 'updateCustomerProfileRequest', $params );
	}
	
	public function updateCustomerPaymentProfile()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
			'paymentProfile'			=> array(
				'billTo'					=> array(
					'firstName'					=> $this->getParameter('billToFirstName'),
					'lastName'					=> $this->getParameter('billToLastName'),
					'company'					=> $this->getParameter('billToCompany'),
					'address'					=> $this->getParameter('billToAddress'),
					'city'						=> $this->getParameter('billToCity'),
					'state'						=> $this->getParameter('billToState'),
					'zip'						=> $this->getParameter('billToZip'),
					'country'					=> $this->getParameter('billToCountry'),
					'phoneNumber'				=> $this->getParameter('billToPhoneNumber'),
					'faxNumber'					=> $this->getParameter('billToFaxNumber'),
				),
				'payment'					=> array(),
				'customerPaymentProfileId'	=> $this->getParameter('customerPaymentProfileId'),
			),
			'validationMode'			=> $this->getParameter('validationMode', 'testMode'),
		);
		
		if( $this->hasParameter('cardNumber') ) {
			$params['paymentProfile']['payment'] = array(
				'creditCard'				=> array(
					'cardNumber'				=> $this->getParameter('cardNumber'),
					'expirationDate'			=> $this->getParameter('expirationDate'),
				),
			);
			
			if( $this->hasParameter('cardCode') ) {
				$params['paymentProfile']['payment']['creditCard']['cardCode'] = $this->getParameter('cardCode');
			}
		}
		elseif( $this->hasParameter('accountNumber') ) {
			$params['paymentProfile']['payment'] = array(
				'bankAccount'				=> array(
					'accountType'				=> $this->getParameter('accountType'),
					'routingNumber'				=> $this->getParameter('routingNumber'),
					'accountNumber'				=> $this->getParameter('accountNumber'),
					'nameOnAccount'				=> $this->getParameter('nameOnAccount'),
					'echeckType'				=> $this->getParameter('echeckType'),
					'bankName'					=> $this->getParameter('bankName'),
				),
			);
		}
		
		return $this->_runTransaction( 'updateCustomerPaymentProfileRequest', $params );
	}
	
	public function updateCustomerShippingAddress()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
			'address'					=> array(
				'firstName'					=> $this->getParameter('shipToFirstName'),
				'lastName'					=> $this->getParameter('shipToLastName'),
				'company'					=> $this->getParameter('shipToCompany'),
				'address'					=> $this->getParameter('shipToAddress'),
				'city'						=> $this->getParameter('shipToCity'),
				'state'						=> $this->getParameter('shipToState'),
				'zip'						=> $this->getParameter('shipToZip'),
				'country'					=> $this->getParameter('shipToCountry'),
				'phoneNumber'				=> $this->getParameter('shipToPhoneNumber'),
				'faxNumber'					=> $this->getParameter('shipToFaxNumber'),
				'customerShippingAddressId'	=> $this->getParameter('customerShippingAddressId'),
			),
		);
		
		return $this->_runTransaction( 'updateCustomerShippingAddressRequest', $params );
	}
	
	public function validateCustomerPaymentProfile()
	{
		$params = array(
			'customerProfileId'			=> $this->getParameter('customerProfileId'),
			'customerPaymentProfileId'	=> $this->getParameter('customerPaymentProfileId'),
			'customerShippingAddressId'	=> $this->getParameter('customerShippingAddressId'),
			'validationMode'			=> $this->getParameter('validationMode'),
		);
		
		return $this->_runTransaction( 'validateCustomerPaymentProfileRequest', $params );
	}
}
