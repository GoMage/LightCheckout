<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.0
 * @since        Class available since Release 1.0
 */

class GoMage_Checkout_Model_Type_Onestep extends Mage_Checkout_Model_Type_Onepage {
	
	protected $country_id;
	protected $mode;
	protected $helper;
	
	public function __construct() {
		
		$this->helper = Mage::helper('gomage_checkout');
		
		if ($this->helper->getIsAnymoreVersion(1, 4)) {
			return parent::__construct();
		}
	}
	
	public function getCustomerSession() {
		return Mage::getSingleton('customer/session');
	}
	
	public function getCheckoutMode() {
		
		if (is_null($this->mode)) {
			$this->mode = $this->helper->getConfigData('general/mode');
		}
		
		return $this->mode;
	
	}
	
	public function getConfigData($node) {
		return $this->helper->getConfigData($node);
	}
	
	public function getDefaultCountryId() {
		
		return $this->helper->getDefaultCountryId();
	
	}
	/**
	 * Validate customer data and set some its data for further usage in quote
	 * Will return either true or array with error messages
	 *
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @return true|array
	 */
	protected function _processValidateCustomer(Mage_Sales_Model_Quote_Address $address) {
		// set customer date of birth for further usage
		$dob = '';
		if ($address->getDob()) {
			$dob = Mage::app()->getLocale()->date($address->getDob(), null, null, false)->toString('yyyy-MM-dd');
			$this->getQuote()->setCustomerDob($dob);
		}
		
		// set customer tax/vat number for further usage
		if ($address->getTaxvat()) {
			$this->getQuote()->setCustomerTaxvat($address->getTaxvat());
		}
		
		// set customer gender for further usage
		if ($address->getGender()) {
			$this->getQuote()->setCustomerGender($address->getGender());
		}
		
		// invoke customer model, if it is registering
		if (self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
			// set customer password hash for further usage
			$customer = Mage::getModel('customer/customer');
			$this->getQuote()->setPasswordHash($customer->encryptPassword($address->getCustomerPassword()));
			
			// validate customer
			foreach (array('firstname' => 'firstname', 'lastname' => 'lastname', 'email' => 'email', 'password' => 'customer_password', 'confirmation' => 'confirm_password', 'taxvat' => 'taxvat', 'gender' => 'gender') as $key => $dataKey) {
				$customer->setData($key, $address->getData($dataKey));
			}
			if ($dob) {
				$customer->setDob($dob);
			}
			
			$validationResult = $customer->validate();
			
			if (true !== $validationResult && is_array($validationResult)) {
				return array('error' => - 1, 'message' => implode(', ', $validationResult));
			}
		}
		elseif (self::METHOD_GUEST == $this->getQuote()->getCheckoutMethod()) {
			$email = $address->getData('email');
			if (! Zend_Validate::is($email, 'EmailAddress')) {
				return array('error' => - 1, 'message' => $this->_helper->__('Invalid email address "%s"', $email));
			}
		}
		
		return true;
	}
	public function saveBilling($data, $customerAddressId) {
		if (empty($data)) {
			return array('error' => - 1, 'message' => $this->helper->__('Invalid data.'));
		}
		
		$address = $this->getQuote()->getBillingAddress();
		
		if (! empty($customerAddressId)) {
			$customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
			if ($customerAddress->getId()) {
				if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
					return array('error' => 1, 'message' => $this->helper->__('Customer Address is not valid.'));
				}
				$address->importCustomerAddress($customerAddress);
			}
		}
		else {
			unset($data['address_id']);
			
			$address->addData($data);
		
		}
		
		$address->implodeStreetAddress();
		
		if ($this->getCheckoutMode() == 1 && intval(Mage::getSingleton('customer/session')->getId()) == 0 && $this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
			return array('error' => 1, 'message' => $this->helper->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address.'));
		}
		
		if (! $address->getCountryId()) {
			
			$address->setCountryId($this->getDefaultCountryId());
		
		}
		
		if (method_exists($this, '_validateCustomerData')) {
			
			$_result = $this->_validateCustomerData($data);
			
			if ($_result !== true) {
				return array('error' => 1, 'message' => $_result['message']);
			}
		
		}
		
		if (($validateRes = $address->validate()) != true) {
			
			return array('error' => 1, 'message' => $validateRes);
		
		}
		else {
			
			$_result = $this->_processValidateCustomer($address);
			
			if ($_result !== true) {
				
				return array('error' => 1, 'message' => $_result['message']);
			
			}
		}
		
		if (! $this->getQuote()->isVirtual()) {
			/**
			 * Billing address using otions
			 */
			$usingCase = isset($data['use_for_shipping']) ? ( int ) $data['use_for_shipping'] : 0;
			
			switch ($usingCase) {
				case 0:
					$shipping = $this->getQuote()->getShippingAddress();
					$shipping->setSameAsBilling(0);
				break;
				case 1:
					$billing = clone $address;
					$billing->unsAddressId()->unsAddressType();
					$shipping = $this->getQuote()->getShippingAddress();
					$shippingMethod = $shipping->getShippingMethod();
					$shipping->addData($billing->getData())->setSameAsBilling(1)->setShippingMethod($shippingMethod)->setCollectShippingRates(true);
					
					if (($validateRes = $shipping->validate()) !== true) {
						
						return array('error' => 1, 'message' => $validateRes);
					}
				
				break;
			}
		
		}
		
		return array();
	}
	
	public function saveShipping($data, $customerAddressId) {
		if (empty($data)) {
			return array('error' => - 1, 'message' => $this->helper->__('Invalid data.'));
		}
		$address = $this->getQuote()->getShippingAddress();
		
		if (! empty($customerAddressId)) {
			$customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
			if ($customerAddress->getId()) {
				if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
					return array('error' => 1, 'message' => $this->helper->__('Customer Address is not valid.'));
				}
				$address->importCustomerAddress($customerAddress);
			}
		}
		else {
			unset($data['address_id']);
			$address->addData($data);
		}
		
		$address->implodeStreetAddress();
		
		if (! $address->getCountryId()) {
			
			$address->setCountryId($this->getDefaultCountryId());
		
		}
		
		$address->setCollectShippingRates(true);
		
		if (($validateRes = $address->validate()) !== true) {
			return array('error' => 1, 'message' => $validateRes);
		}
		
		return array();
	}
	
	protected function _customerEmailExists($email, $websiteId = null) {
		$customer = Mage::getModel('customer/customer');
		if ($websiteId) {
			$customer->setWebsiteId($websiteId);
		}
		$customer->loadByEmail($email);
		if ($customer->getId()) {
			return $customer;
		}
		return false;
	}
	
	public function shippingAsBilling() {
		
		if (null == $this->getCheckout()->getShippingSameAsBilling()) {
			return true;
		}
		
		return ( bool ) $this->getCheckout()->getShippingSameAsBilling();
	
	}
	
	public function initCheckout() {
		
		if (Mage::getSingleton('checkout/session')->getCheckoutState() !== Mage_Checkout_Model_Session::CHECKOUT_STATE_BEGIN) {
			Mage::getSingleton('checkout/session')->resetCheckout();
		}
		
		if (is_null(Mage::getSingleton('customer/session')->getCreateAccount())) {
			
			Mage::getSingleton('customer/session')->setCreateAccount(( bool ) $this->helper->getConfigData('registration/account_checkbox'));
		
		}
		
		if ($shipping_address = $this->getQuote()->getShippingAddress()) {
			
			$shipping_address_mode = Mage::getSingleton('checkout/session')->getShippingSameAsBilling();
			
			if (is_null($shipping_address_mode)) {
				
				switch (Mage::helper('gomage_checkout')->getConfigData('general/different_shipping_enabled')) :
					
					default:
						Mage::getSingleton('checkout/session')->setShippingSameAsBilling(true);
					break;
					
					case (2):
						Mage::getSingleton('checkout/session')->setShippingSameAsBilling(false);
					break;
				
				endswitch
				;
			
			}
		
		}
		
		$checkout = $this->getCheckout();
		$customer = $this->getCustomerSession()->getCustomer();
		
		if ($customer->getId() > 0 && ! $checkout->getCustomerAssignedQuote()) {
			
			$this->getQuote()->assignCustomer($customer);
			
			$countryId = $this->getDefaultCountryId();
			
			if ($customer->getDefaultBillingAddress() == false) {
				
				$address = Mage::getModel('sales/quote_address');
				$address->setFirstname($customer->getFirstname());
				$address->setLastname($customer->getLastname());
				$address->setMiddlename($customer->getMiddlename());
				$address->setPrefix($customer->getPrefix());
				$address->setSuffix($customer->getSuffix());
				$address->setCountryId($countryId);
				
				if ((Mage::getStoreConfig('gomage_checkout/geoip/geoip_city_enabled') || Mage::getStoreConfig('gomage_checkout/geoip/geoip_post_enabled') || Mage::getStoreConfig('gomage_checkout/geoip/geoip_state_enabled')) && file_exists(Mage::getBaseDir('media') . "/geoip/GeoLiteCity.dat") && extension_loaded('mbstring')) {
					
					$record = $this->helper->getGeoipRecord();
					if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_city_enabled')) {
						$address->setCity($record->city);
					}
					if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_post_enabled')) {
						$address->setPostcode($record->postal_code);
					}
					if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_state_enabled')) {
						$address->setRegionId($this->prepareRegion($record));
					}
				}
				
				$this->getQuote()->setBillingAddress($address);
			}
			else {
				
				if (! $this->getQuote()->getBillingAddress()->getCountryId()) {
					
					$this->getQuote()->getBillingAddress()->setCountryId($countryId);
					
					if (! $this->getQuote()->getBillingAddress()->getCity()) {
						
						if ((Mage::getStoreConfig('gomage_checkout/geoip/geoip_city_enabled') || Mage::getStoreConfig('gomage_checkout/geoip/geoip_post_enabled') || Mage::getStoreConfig('gomage_checkout/geoip/geoip_state_enabled')) && file_exists(Mage::getBaseDir('media') . "/geoip/GeoLiteCity.dat") && extension_loaded('mbstring')) {
							
							$record = $this->helper->getGeoipRecord();
							if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_city_enabled')) {
								$this->getQuote()->getBillingAddress()->setCity($record->city);
							}
							if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_post_enabled')) {
								$this->getQuote()->getBillingAddress()->setPostcode($record->postal_code);
							}
							if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_state_enabled')) {
								$this->getQuote()->getBillingAddress()->setRegionId($this->prepareRegion($record));
							}
						
						}
					
					}
				
				}
			
			}
			
			if (! $this->getQuote()->isVirtual()) {
				
				if ($customer->getDefaultShippingAddress() == false || $this->shippingAsBilling()) {
					
					if ($customer->getDefaultBillingAddress()) {
						$shippingAddress = Mage::getModel('sales/quote_address')->importCustomerAddress($customer->getDefaultBillingAddress());
						$this->getQuote()->setShippingAddress($shippingAddress);
					}
					else {
						$address = Mage::getModel('sales/quote_address');
						$address->setFirstname($customer->getFirstname());
						$address->setLastname($customer->getLastname());
						$address->setMiddlename($customer->getMiddlename());
						$address->setPrefix($customer->getPrefix());
						$address->setSuffix($customer->getSuffix());
						$address->setCountryId($countryId);
						
						if ((Mage::getStoreConfig('gomage_checkout/geoip/geoip_city_enabled') || Mage::getStoreConfig('gomage_checkout/geoip/geoip_post_enabled') || Mage::getStoreConfig('gomage_checkout/geoip/geoip_state_enabled')) && file_exists(Mage::getBaseDir('media') . "/geoip/GeoLiteCity.dat") && extension_loaded('mbstring')) {
							
							$record = $this->helper->getGeoipRecord();
							if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_city_enabled')) {
								$address->setCity($record->city);
							}
							if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_post_enabled')) {
								$address->setPostcode($record->postal_code);
							}
							if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_state_enabled')) {
								$address->setRegionId($this->prepareRegion($record));
							}
						}
						$this->getQuote()->setShippingAddress($address);
					}
				
				}
			}
			
			$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
			
			$checkout->setCustomerAssignedQuote(true);
		
		}
		else {
			
			if (! $this->getCustomerSession()->isLoggedIn()) {
				$countryId = $this->getDefaultCountryId();
				
				if (! $this->getQuote()->getBillingAddress()->getCountryId()) {
					
					$this->getQuote()->getBillingAddress()->setCountryId($countryId);
					
					if (! $this->getQuote()->getBillingAddress()->getCity()) {
						
						if ((Mage::getStoreConfig('gomage_checkout/geoip/geoip_city_enabled') || Mage::getStoreConfig('gomage_checkout/geoip/geoip_post_enabled') || Mage::getStoreConfig('gomage_checkout/geoip/geoip_state_enabled')) && file_exists(Mage::getBaseDir('media') . "/geoip/GeoLiteCity.dat") && extension_loaded('mbstring')) {
							
							$record = $this->helper->getGeoipRecord();
							if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_city_enabled')) {
								$this->getQuote()->getBillingAddress()->setCity($record->city);
							}
							if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_post_enabled')) {
								$this->getQuote()->getBillingAddress()->setPostcode($record->postal_code);
							}
							if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_state_enabled')) {
								$this->getQuote()->getBillingAddress()->setRegionId($this->prepareRegion($record));
							}
						
						}
					
					}
				
				}
				
				if (! $this->getQuote()->isVirtual()) {
					
					if (! $this->getQuote()->getShippingAddress()->getCountryId()) {
						
						$this->getQuote()->getShippingAddress()->setCountryId($countryId);
						
						if (! $this->getQuote()->getShippingAddress()->getCity()) {
							
							if ((Mage::getStoreConfig('gomage_checkout/geoip/geoip_city_enabled') || Mage::getStoreConfig('gomage_checkout/geoip/geoip_post_enabled') || Mage::getStoreConfig('gomage_checkout/geoip/geoip_state_enabled')) && file_exists(Mage::getBaseDir('media') . "/geoip/GeoLiteCity.dat") && extension_loaded('mbstring')) {
								
								$record = $this->helper->getGeoipRecord();
								if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_city_enabled')) {
									$this->getQuote()->getShippingAddress()->setCity($record->city);
								}
								if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_post_enabled')) {
									$this->getQuote()->getShippingAddress()->setPostcode($record->postal_code);
								}
								if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_state_enabled')) {
									$this->getQuote()->getShippingAddress()->setRegionId($this->prepareRegion($record));
								}
							
							}
						
						}
					
					}
				
				}
				$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
			}
		
		}
		
		if (! $this->getQuote()->isVirtual()) {
			
			if (! $this->getQuote()->getShippingAddress()->getShippingMethod() && ($shippingMethod = $this->helper->getDefaultShippingMethod())) {
				
				$this->getQuote()->getShippingAddress()->collectShippingRates();
				$this->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
				$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
			
			}
		
		}
		
		$verify_result = Mage::helper('gomage_checkout')->getConfigData('vat/enabled') && $this->verifyCustomerVat();
		$flag = intval(Mage::helper('gomage_checkout')->getConfigData('vat/show_checkbox'));
				
		if ($this->getQuote()->getBillingAddress()->getBuyWithoutVat() === null) {									
			$this->getQuote()->getBillingAddress()->setBuyWithoutVat($flag == GoMage_Checkout_Model_Adminhtml_System_Config_Source_Checkboxtax::YES_CHECKED);
			$this->getQuote()->getShippingAddress()->setBuyWithoutVat($flag == GoMage_Checkout_Model_Adminhtml_System_Config_Source_Checkboxtax::YES_CHECKED);		
		}
		
		if ($verify_result && $flag == GoMage_Checkout_Model_Adminhtml_System_Config_Source_Checkboxtax::NO){
			$this->getQuote()->getBillingAddress()->setBuyWithoutVat(true);
			$this->getQuote()->getShippingAddress()->setBuyWithoutVat(true);
		}
		
		$paymentMethod = $this->getQuote()->getPayment()->getMethod();
		if (!$paymentMethod){
			if(Mage::helper('gomage_checkout')->getDefaultPaymentMethod()){
				$paymentMethod = Mage::helper('gomage_checkout')->getDefaultPaymentMethod();
			}else{	
	            $payment_methods = Mage::helper('payment')->getStoreMethods($this->getQuote()->getStoreId(), $this->getQuote());
	            if (count($payment_methods) == 1){
	            	$paymentMethod = $payment_methods[0];
	            	$paymentMethod = $paymentMethod->getCode();
	            }
			}
		}	
		try {
			if ($paymentMethod) {
				$this->getQuote()->getPayment()->importData(array('method' => $paymentMethod));
			}
		}
		catch (Exception $_e) {
		}
		
		if (! $this->getQuote()->getShippingAddress()->getShippingMethod()) {
			$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
			$this->getQuote()->getShippingAddress()->collectShippingRates();
			$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
		}
		
		if (Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 11)) {
			$items = $this->getQuote()->getAllItems();
			foreach ($items as $item) {
				$item->setGwId(null)->save();
			}
		}
		
		if (Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 11)) {
			//reset gift wrapping params
			$wrappingInfo = array('gw_id' => false, 'gw_allow_gift_receipt' => false, 'gw_add_card' => false);
			if ($this->getQuote()->getShippingAddress()) {
				$this->getQuote()->getShippingAddress()->addData($wrappingInfo);
			}
			if ($this->getQuote()->getBillingAddress()) {
				$this->getQuote()->getBillingAddress()->addData($wrappingInfo);
			}
			$this->getQuote()->addData($wrappingInfo);
		}
		
		$this->getQuote()->setTotalsCollectedFlag(false);
		$this->getQuote()->collectTotals()->save();
		
		return $this;
	}
	
	public function savePaymentMethod($method) {
		
		if (! empty($method)) {			
			$this->getQuote()->getPayment()->importData($method);		
		}
	
	}
	
	public function saveShippingMethod($method) {
		
		if (! empty($method)) {			
			$this->getQuote()->getShippingAddress()->setShippingMethod($method);		
		}
	
	}
	
	public function verifyCustomerVat() {
		
		$vat_number = trim($this->getQuote()->getBillingAddress()->getTaxvat() !== null ? $this->getQuote()->getBillingAddress()->getTaxvat() : $this->getQuote()->getCustomerTaxvat());
		
		if ($vat_number) {
			
			$this->getQuote()->getBillingAddress()->setTaxvat($vat_number);
			$this->getQuote()->getShippingAddress()->setTaxvat($vat_number);
			
			$vat_number = preg_replace('/^\D{0,2}/', '', $vat_number);
			
			$country = $this->getQuote()->getBillingAddress()->getCountry();
			if ($country == "GR") {
				$country = "EL";
			}
			
			if (in_array($country, array("AT", "BE", "BG", "CY", "CZ", "DE", "DK", "EE", "EL", "ES", "FI", "FR", "GB", "HU", "IE", "IT", "LT", "LU", "LV", "MT", "NL", "PL", "PT", "RO", "SE", "SI", "SK"))) {
				try {
					
					if ($this->helper->getConfigData('vat/vat_verification') == GoMage_Checkout_Model_Adminhtml_System_Config_Source_Vatverification::ISVAT) {
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, 'http://isvat.appspot.com/' . $country . '/' . $vat_number . '/');
						curl_setopt($ch, CURLOPT_TIMEOUT, 30);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						$content = curl_exec($ch);
						curl_close($ch);
						
						if (strpos($content, "true") === false) {
							$vat_exemption_flag = false;
						}
						else {
							$vat_exemption_flag = true;
						}
					}
					else {
						$ch = new SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');
	                    $ch_data = array('countryCode' => $country, 'vatNumber' => $vat_number);
	                    $content = $ch->checkVat($ch_data);
	                    $vat_exemption_flag = $content->valid;
					}
					
					$this->getQuote()->getBillingAddress()->setIsValidVat($vat_exemption_flag);
					$this->getQuote()->getShippingAddress()->setIsValidVat($vat_exemption_flag);
					
					return $vat_exemption_flag;
				}
				catch (Exception $e) {
				
				}
			}
		
		}
		
		$this->getQuote()->getBillingAddress()->setIsValidVat(null);
		$this->getQuote()->getShippingAddress()->setIsValidVat(null);
		
		return false;
	
	}

    private function prepareRegion($record){

        if ($record->country_code && $record->region){
          $country = Mage::getModel('directory/country')->loadByCode($record->country_code);
          if ($country && $country->getId()){
            $region = Mage::getModel('directory/region')->loadByCode($record->region, $country->getId());
            if ($region && $region->getId()){
              return $region->getId();
            }
          }
        }

        return $record->region;

    }

}
