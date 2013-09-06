<?php

class GoMage_Checkout_Model_Tax_Calculation extends Mage_Tax_Model_Calculation{
	
    /**
     * Get request object with information necessary for getting tax rate
     * Request object contain:
     *  country_id (->getCountryId())
     *  region_id (->getRegionId())
     *  postcode (->getPostcode())
     *  customer_class_id (->getCustomerClassId())
     *  store (->getStore())
     *
     * @param   null|false|Varien_Object $shippingAddress
     * @param   null|false|Varien_Object $billingAddress
     * @param   null|int $customerTaxClass
     * @param   null|int $store
     * @return  Varien_Object
     */
    public function getRateRequest($shippingAddress = null, $billingAddress = null, $customerTaxClass = null, $store = null)
    {
        if ($shippingAddress === false && $billingAddress === false && $customerTaxClass === false) {
            return $this->getRateOriginRequest($store);
        }
        $address    = new Varien_Object();
        $customer   = $this->getCustomer();
        $basedOn    = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $store);

        if (($shippingAddress === false && $basedOn == 'shipping')
            || ($billingAddress === false && $basedOn == 'billing')) {
            $basedOn = 'default';
        } else {
            if ((($billingAddress === false || is_null($billingAddress) || !$billingAddress->getCountryId()) && $basedOn == 'billing')
                || (($shippingAddress === false || is_null($shippingAddress) || !$shippingAddress->getCountryId()) && $basedOn == 'shipping')){
                if ($customer) {
                    $defBilling = $customer->getDefaultBillingAddress();
                    $defShipping = $customer->getDefaultShippingAddress();

                    if ($basedOn == 'billing' && $defBilling && $defBilling->getCountryId()) {
                        $billingAddress = $defBilling;
                    } else if ($basedOn == 'shipping' && $defShipping && $defShipping->getCountryId()) {
                        $shippingAddress = $defShipping;
                    } else {
                        $basedOn = 'default';
                    }
                } else {
                    $basedOn = 'default';
                }
            }
        }

        switch ($basedOn) {
            case 'billing':
                $address = $billingAddress;
                break;
            case 'shipping':
                $address = $shippingAddress;
                break;
            case 'origin':
                $address = $this->getRateOriginRequest($store);
                break;
            case 'default':
                $address->setCountryId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_COUNTRY, $store))
                    ->setRegionId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_REGION, $store))
                    ->setPostcode(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_POSTCODE, $store));
                break;
        }
        
        
        

        if (is_null($customerTaxClass) && $customer) {
            $customerTaxClass = $customer->getTaxClassId();
        } elseif (($customerTaxClass === false) || !$customer) {
            $customerTaxClass = $this->getDefaultCustomerTaxClass($store);
        }

        $request = new Varien_Object();
        
        if($address->getBuyWithoutVat()>0){
        	
        	$mode = null;
        	
        	if($address->getCountry() == Mage::helper('gomage_checkout')->getConfigData('vat/country')){
        		
        		$mode = Mage::helper('gomage_checkout')->getVatBaseCountryMode();
        		
        	}elseif(in_array($address->getCountry(), array("AT","BE","BG","CY","CZ","DE","DK","EE","EL","ES","FI","FR","GB","HU","IE","IT","LT","LU","LV","MT","NL","PL","PT","RO","SE","SI","SK"))){
        		
        		$mode = Mage::helper('gomage_checkout')->getVatWithinCountryMode();
        		
        	}
        	
        	if($mode){
        		
        		$rule_id = intval(Mage::helper('gomage_checkout')->getConfigData('vat/rule'));
        		
        		if($rule_id){
        		
        		switch($mode){
        			
        			case(1):
        				if($address->getIsValidVat()>0){
        					$request->setDisableByRule($rule_id);
        				}
        			break;
        			case(2):
        				$request->setDisableByRule($rule_id);
        			break;
        			
        		}
        		
        		}
        	}
        	
        	
        }
        
        $request
            ->setCountryId($address->getCountryId())
            ->setRegionId($address->getRegionId())
            ->setPostcode($address->getPostcode())
            ->setStore($store)
            ->setCustomerClassId($customerTaxClass);
        return $request;
    }
    
}
