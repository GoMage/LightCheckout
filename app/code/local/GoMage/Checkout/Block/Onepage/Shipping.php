<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.7
 * @since        Class available since Release 1.0
 */ 

class GoMage_Checkout_Block_Onepage_Shipping extends GoMage_Checkout_Block_Onepage_Abstract{
	
	protected $prefix = 'shipping';
	
	public function customerHasAddresses(){
		
		if(intval($this->helper->getConfigData('address_fields/address_book'))){
			
			return parent::customerHasAddresses();
			
		}
		return false;
		
	}
	
    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    public function getAddress()
    {
    	
    	if($this->asBilling()){
    		
    		$customer = $this->getQuote()->getCustomer();
    		
    		if($customer->getId() > 0){
    			
    			if($address = $customer->getDefaultShippingAddress()){
    				
    				return $address;
    				
    			}
    			
    			
    		}
    		
    	}
    	
        return $this->getQuote()->getShippingAddress();
        
    }
	
	
	public function asBilling(){
    	
    	if(null === $this->getCheckout()->getShippingSameAsBilling()){
    		return true;
    	}
    	
    	return (bool)$this->getCheckout()->getShippingSameAsBilling();
    	
    }
	
    /**
     * Retrieve is allow and show block
     *
     * @return bool
     */
    public function isShow()
    {
        return !$this->getQuote()->isVirtual();
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
        
        $options = $this->getCountryOptions();
        
        $options[0] = array('value'=>'', 'label'=>$this->__('--Please Select--'));
        
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.'_country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('required-entry absolute-advice')
            ->setValue($countryId)
            ->setOptions($options);
        

        return $select->getHtml();
    }
}