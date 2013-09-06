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

class GoMage_Checkout_Helper_Data extends Mage_Core_Helper_Abstract{
	
	protected $mode;
	protected $country_id;
	
    public function getConfigData($node){
		return Mage::getStoreConfig('gomage_checkout/'.$node);
	}
	public function getCheckoutMode(){
		
		
		
		if(is_null($this->mode)){
			
			if(Mage::getSingleton('checkout/type_onepage')->getQuote()->isAllowedGuestCheckout()){
			
				$this->mode = intval($this->getConfigData('registration/mode'));
			
			}else{
				
				$this->mode = 1;
				
			}
			
			
			
		}
		
		return $this->mode;
		
	}
	public function getDefaultCountryId(){
		
		
		
		if(is_null($this->country_id)){
			
			
			if(Mage::getStoreConfig('gomage_checkout/general/geoip_enabled') && file_exists(Mage::getBaseDir('media')."/geoip/GeoLiteCity.dat") && extension_loaded('mbstring')){
				
				
				try{
				$this->country_id = GeoIP_Core::getInstance(Mage::getBaseDir('media')."/geoip/GeoLiteCity.dat", GeoIP_Core::GEOIP_STANDARD)->geoip_country_code_by_addr($_SERVER['REMOTE_ADDR']);
				}catch(Exception $e){
					
					echo $e;
					
				}
			}
			
			if (!$this->country_id) {
				
				$this->country_id = Mage::getStoreConfig('gomage_checkout/general/default_country');
		    	
		        if (!$this->country_id) {
		            $this->country_id = Mage::getStoreConfig('general/country/default');
		        }
	        }
        
        }
        return $this->country_id;
        
	}
	public function getDefaultShippingMethod(){
		
		$address = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
		
		$address->setCollectShippingRates(true)->collectShippingRates();
		
		$rates = $address->getGroupedAllShippingRates();
		
        if(count($rates) == 1){
						
			foreach($rates as $rate_code=>$methods){
				
				if(count($methods) == 1){
					
					foreach($methods as $method){
						
						return $method->getCode();
					}
					
				}else{
					
					return $this->getConfigData('general/default_shipping_method');
					
				}
				
				
			}
			
		}else{
		
			return $this->getConfigData('general/default_shipping_method');
		
		}
	}
	public function getDefaultPaymentMethod(){
		return $this->getConfigData('general/default_payment_method');
	}
	public function getActivePaymentMethods($store=null)
    {
        $methods = array();
        $config = Mage::getStoreConfig('payment', $store);
        
        foreach ($config as $code => $methodConfig) {
        	
        	if(isset($methodConfig['model']) && $methodConfig['model']){
        	
        	if(isset($methodConfig['group']) && $methodConfig['group'] == 'mbookers' && Mage::getStoreConfigFlag('moneybookers/'.$code.'/active', $store)){
        		
        		$method = $this->_getPaymentMethod($code, $methodConfig);
        		$method['group'] = 'mbookers';
        		
        		$methods[$code] = $method;
        	}elseif($methodConfig['model'] == 	'googlecheckout/payment'){
        		
        		if(Mage::getStoreConfigFlag('google/checkout/active', $store)){
        			$method = $this->_getPaymentMethod($code, $methodConfig);
        			$methods[$code] = $method;
        		}
        		
        	}elseif (Mage::getStoreConfigFlag('payment/'.$code.'/active', $store)) {
                $method = $this->_getPaymentMethod($code, $methodConfig);
        		$method['group'] = '';
        		
        		$methods[$code] = $method;
            }
            
            }
        }
        
        
        return $methods;
    }
    
    protected function _getPaymentMethod($code, $config, $store=null)
    {
        $modelName = $config['model'];
        $method = Mage::getModel($modelName);
        $method->setId($code)->setStore($store);
        return $method;
    }
	
}
