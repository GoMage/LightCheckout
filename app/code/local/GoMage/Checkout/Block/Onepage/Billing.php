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


class GoMage_Checkout_Block_Onepage_Billing extends GoMage_Checkout_Block_Onepage_Abstract{
	
	protected $prefix = 'billing';
	
	protected function _prepareLayout(){
		
		return parent::_prepareLayout();
		
	}
	
    public function getCountries()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    function getAddress() {
        return $this->getQuote()->getBillingAddress();
    }

    public function getFirstname()
    {
        $firstname = $this->getAddress()->getFirstname();
        if (empty($firstname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getFirstname();
        }
        return $firstname;
    }

    public function getLastname()
    {
        $lastname = $this->getAddress()->getLastname();
        if (empty($lastname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getLastname();
        }
        return $lastname;
    }
    
    public function shippingAsBilling(){
    	
    	if(is_null($this->getCheckout()->getShippingSameAsBilling())){
    		return true;
    	}
    	
    	return (bool)($this->getCheckout()->getShippingSameAsBilling());
    	
    }

    public function canShip()
    {
    	
        if(!$this->getQuote()->isVirtual()){
        	
        	if(intval($this->getConfigData('general/different_shipping_enabled'))){
        		
        		return true;
        		
        	}
        	
        }
        return false;
    }
    
    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getAddress()->getCountryId();
        
        if (is_null($countryId)) {
        	$countryId = $this->getConfigData('general/default_country');
        }
        
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        
        
        
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.'_country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate[required]')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());


        return $select->getHtml();
    }
    
    
    

}
