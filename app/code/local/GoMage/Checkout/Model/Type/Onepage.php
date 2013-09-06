<?php
 /**
 * GoMage.com
 *
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */

/**
 * One page checkout processing model
 */
class GoMage_Checkout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
	
	protected $country_id;
	protected $mode;
	protected $helper;
	
	public function __construct(){
		
		$this->helper = Mage::helper('gomage_checkout');
		
		
		if(Mage::getVersion() >= '1.4'){
			return parent::__construct();
		}
	}
	
	public function getCustomerSession(){
		return Mage::getSingleton('customer/session');
	}
	
	public function getCheckoutMode(){
		
		if(is_null($this->mode)){
			$this->mode = $this->helper->getConfigData('general/mode');
		}
		
		return $this->mode;
		
	}
	
	public function getConfigData($node){
		return $this->helper->getConfigData($node);
	}
	
	public function getDefaultCountryId(){
		
        return $this->helper->getDefaultCountryId();
        
	}
	
	public function saveBilling($data, $customerAddressId)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->helper->__('Invalid data.'));
        }
		
        $address = $this->getQuote()->getBillingAddress();
        
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => $this->helper->__('Customer Address is not valid.')
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            unset($data['address_id']);
            
            
            $address->addData($data);
            
        }
        
        $address->implodeStreetAddress();
        
        if ($this->getCheckoutMode() != 2 && intval(Mage::getSingleton('customer/session')->getId()) == 0 && $this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
            return array('error' => 1, 'message' => $this->helper->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address.'));
        }
		
		if(!$address->getCountryId()){
        	
        	$address->setCountryId($this->getDefaultCountryId());
        	
        }
		    
		
		
        if (($validateRes = $address->validate())!=true) {
        	
        	
            return array('error' => 1, 'message' => $validateRes);
        }
        
        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using otions
             */
            $usingCase = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;

            switch($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping =$this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();
                    $shipping->addData($billing->getData())
                        ->setSameAsBilling(1)
                        ->setShippingMethod($shippingMethod)
                        ->setCollectShippingRates(true);
                    
                    if (($validateRes = $shipping->validate())!==true) {
			     
			        	
			            return array('error' => 1, 'message' => $validateRes);
			        }
                    
                    break;
            }
            
            
            
        }
        
        
        /*
        if (true !== $result = $this->_processValidateCustomer($address)) {
            return $result;
        }
        */
        
        return array();
    }
	
	public function saveShipping($data, $customerAddressId)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->helper->__('Invalid data.'));
        }
        $address = $this->getQuote()->getShippingAddress();

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => $this->helper->__('Customer Address is not valid.')
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            unset($data['address_id']);
            $address->addData($data);
        }
        
        $address->implodeStreetAddress();
        
        if(!$address->getCountryId()){
        	
        	$address->setCountryId($this->getDefaultCountryId());
        	
        }
        
        $address->setCollectShippingRates(true);

        if (($validateRes = $address->validate())!==true) {
            return array('error' => 1, 'message' => $validateRes);
        }
        
        return array();
    }
    
    protected function _customerEmailExists($email, $websiteId = null)
    {
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
	
	public function shippingAsBilling(){
    	
    	if(null == $this->getCheckout()->getShippingSameAsBilling()){
    		return true;
    	}
    	
    	return (bool)$this->getCheckout()->getShippingSameAsBilling();
    	
    }
	
    public function initCheckout()
    {
    	
        $checkout = $this->getCheckout();
        $customer = $this->getCustomerSession()->getCustomer();
        
        if ($customer->getId() > 0 && !$checkout->getCustomerAssignedQuote()) {
        	
            $this->getQuote()->assignCustomer($customer);
            
            if($customer->getDefaultBillingAddress() == false){
            	
            	$address = Mage::getModel('sales/quote_address');
            	$address->setFirstname($customer->getFirstname());
            	$address->setLastname($customer->getLastname());
            	$address->setMiddlename($customer->getMiddlename());
            	$address->setPrefix($customer->getPrefix());
            	$address->setSuffix($customer->getSuffix());
            	
            	$this->getQuote()->setBillingAddress($address);
            }
            
            if (!$this->getQuote()->isVirtual()){
            	
            	if($customer->getDefaultShippingAddress() == false || $this->shippingAsBilling()){
            		
            		if($customer->getDefaultBillingAddress()){
            		
            		$shippingAddress = Mage::getModel('sales/quote_address')->importCustomerAddress($customer->getDefaultBillingAddress());
            		$this->getQuote()->setShippingAddress($shippingAddress);
            		
            		}
            		
            	}
            	
            	$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            	
            }
            $checkout->setCustomerAssignedQuote(true);
            //$this->getQuote()->collectTotals()->save();
        }else{
        	
        	
        	if(!$this->getCustomerSession()->isLoggedIn()){
	        	$countryId = $this->getDefaultCountryId();
				
				if(!$this->getQuote()->getBillingAddress()->getCountryId()){
					
			        $this->getQuote()->getBillingAddress()->setCountryId($countryId);
			        
				}
				
				if (!$this->getQuote()->isVirtual()){

					if(!$this->getQuote()->getShippingAddress()->getCountryId()){
						
				        $this->getQuote()->getShippingAddress()->setCountryId($countryId);
						
					}
					
				}
				$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
			}
			
        }
        
        if(!$this->getQuote()->isVirtual()){
        
        if(!$this->getQuote()->getShippingAddress()->getShippingMethod() && ($shippingMethod = $this->helper->getDefaultShippingMethod())){
    		
    		$this->getQuote()->getShippingAddress()->collectShippingRates();
    		$this->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
    		
    	}
    	
    	}
    	
    	
    	$this->getQuote()->collectTotals()->save();
    	
    	
        
        return $this;
    }

}
