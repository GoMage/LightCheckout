<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
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
			
			if(Mage::getSingleton('gomage_checkout/type_onestep')->getQuote()->isAllowedGuestCheckout()){
			
				$this->mode = intval($this->getConfigData('registration/mode'));
			
			}else{
				
				$this->mode = 1;
				
			}
			
		}
		
		return $this->mode;
		
	}
	
	public function getAllStoreDomains(){
		
		$domains = array();
    	
    	foreach (Mage::app()->getWebsites() as $website) {
    		
    		$url = $website->getConfig('web/unsecure/base_url');
    		
    		if($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))){
    		
    		$domains[] = $domain;
    		
    		}
    		
    		$url = $website->getConfig('web/secure/base_url');
    		
    		if($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))){
    		
    		$domains[] = $domain;
    		
    		}
    		
    	}
    	
    	return array_unique($domains);
    	
		
	}
	
	public function getAvailabelWebsites(){
		return $this->_w();
	}
	public function getAvailavelWebsites(){
		return $this->_w();
	}
	
	protected function _w(){
    
        if(!Mage::getStoreConfig('gomage_activation/lightcheckout/installed') || 
           (intval(Mage::getStoreConfig('gomage_activation/lightcheckout/count')) > 10))
		{
			return array();
		}
		            		
		$time_to_update = 60*60*24*15;
		
		$r = Mage::getStoreConfig('gomage_activation/lightcheckout/ar');
		$t = Mage::getStoreConfig('gomage_activation/lightcheckout/time');
		$s = Mage::getStoreConfig('gomage_activation/lightcheckout/websites');
		
		$last_check = str_replace($r, '', Mage::helper('core')->decrypt($t));
		
		$allsites = explode(',', str_replace($r, '', Mage::helper('core')->decrypt($s)));
		$allsites = array_diff($allsites, array(""));
			
		if(($last_check+$time_to_update) < time()){
			$this->a(Mage::getStoreConfig('gomage_activation/lightcheckout/key'), 
			         intval(Mage::getStoreConfig('gomage_activation/lightcheckout/count')),
			         implode(',', $allsites));
		}
		
		return $allsites;
		
	}
	
	public function a($k, $c = 0, $s = ''){
		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://www.gomage.com/index.php/gomage_downloadable/key/check'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key='.urlencode($k).'&sku=lightcheckout&domains='.urlencode(implode(',', $this->getAllStoreDomains())));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        $content = curl_exec($ch);
               
        $r	= Zend_Json::decode($content);
        $e = Mage::helper('core');
                
        if(empty($r)){
        	
        	$value1 = Mage::getStoreConfig('gomage_activation/lightcheckout/ar');
        	
	        $groups = array(
	        	'lightcheckout'=>array(
	        		'fields'=>array(
	        			'ar'=>array(
	        				'value'=>$value1
	        			),
	        			'websites'=>array(
	        				'value'=>(string)Mage::getStoreConfig('gomage_activation/lightcheckout/websites')
	        			),
	        			'time'=>array(
	        				'value'=>(string)$e->encrypt($value1.(time()-(60*60*24*15-1800)).$value1)
	        			),
	        			'count'=>array(
	        				'value'=>$c+1)	        			
	        		)
	        	)
        	);
        	
	        Mage::getModel('adminhtml/config_data')
	                ->setSection('gomage_activation')
	                ->setGroups($groups)
	                ->save();
	                
	        Mage::getConfig()->reinit();
            Mage::app()->reinitStores();        
        	
        	return;
        }
        
        $value1 = '';
        $value2 = '';                
        
        if(isset($r['d']) && isset($r['c'])){
    		$value1 = $e->encrypt(base64_encode(Zend_Json::encode($r)));
        
        if (!$s) $s = Mage::getStoreConfig('gomage_activation/lightcheckout/websites');
        
        $s = array_slice(explode(',', $s), 0, $r['c']);
        
        $value2 = $e->encrypt($value1.implode(',', $s).$value1);
        
        }
        $groups = array(
        	'lightcheckout'=>array(
        		'fields'=>array(
        			'ar'=>array(
        				'value'=>$value1
        			),
        			'websites'=>array(
        				'value'=>(string)$value2
        			),
        			'time'=>array(
        				'value'=>(string)$e->encrypt($value1.time().$value1)
        			),
        			'installed'=>array(
        				'value'=>1
        			),
        			'count'=>array(
	        				'value'=>0)
        		)
        	)
        );
        
        Mage::getModel('adminhtml/config_data')
                ->setSection('gomage_activation')
                ->setGroups($groups)
                ->save();

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();        
        
	}
	
	public function ga(){
		return Zend_Json::decode(base64_decode(Mage::helper('core')->decrypt(Mage::getStoreConfig('gomage_activation/lightcheckout/ar'))));
	}
	
	public function getGeoipRecord(){
		
		return GeoIP_Core::getInstance(Mage::getBaseDir('media')."/geoip/GeoLiteCity.dat", GeoIP_Core::GEOIP_STANDARD)->geoip_record_by_addr(Mage::helper('core/http')->getRemoteAddr());
		
	}
	
	public function getDefaultCountryId(){
		
		
		
		if(is_null($this->country_id)){
			
			
			if(Mage::getStoreConfig('gomage_checkout/geoip/geoip_enabled') && file_exists(Mage::getBaseDir('media')."/geoip/GeoLiteCity.dat") && extension_loaded('mbstring')){
				
				
				try{
					
					$this->country_id = GeoIP_Core::getInstance(Mage::getBaseDir('media')."/geoip/GeoLiteCity.dat", GeoIP_Core::GEOIP_STANDARD)->geoip_country_code_by_addr(Mage::helper('core/http')->getRemoteAddr());
					
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
        
        if($method){
        $method->setId($code)->setStore($store);
        }
        return $method;
    }
    
    public function getVatBaseCountryMode(){
    	return $this->getConfigData('vat/base_country');
    }
    
    public function getVatWithinCountryMode(){
    	return $this->getConfigData('vat/if_not_base_country');
    }
    
    public function getTaxCountries(){
    	
    	if($rule_ids = Mage::helper('gomage_checkout')->getConfigData('vat/rule')){
    	
    	$resource	= Mage::getSingleton('core/resource');
    	$connection	= $resource->getConnection('read');
    	
    	$q = sprintf('SELECT DISTINCT(`tax_country_id`) FROM `%s` WHERE `tax_calculation_rate_id` IN (SELECT `tax_calculation_rate_id` FROM `%s` WHERE `tax_calculation_rule_id` in (%s) )', $resource->getTableName('tax_calculation_rate'), $resource->getTableName('tax_calculation'), $rule_ids);
    	
    	return (array)$connection->fetchCol($q);
    	
    	}
    	
    	return array();
    	
    }
    
    public function getIsAnymoreVersion($major, $minor, $revision = 0)
    {
        $version_info = Mage::getVersion();
        $version_info = explode('.', $version_info);
        
        if ($version_info[0] > $major)
        {
           return true;
        }   
        elseif ($version_info[0] == $major)
        {
            if ($version_info[1] > $minor)
            {
                return true; 
            }
            elseif ($version_info[1] == $minor)
            {
                if ($version_info[2] >= $revision)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }        
        }
        else
        { 
           return false;
        }                                
    }
    
    public function isMobileDevice(){

    	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    	if (!$user_agent || strpos($user_agent, 'ipad')) return false;
    	
		$regex_match="/(nokia|iphone|android|motorola|^mot-|softbank|foma|docomo|kddi|up.browser|up.link|";
		$regex_match.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
		$regex_match.="blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";	
		$regex_match.="symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte-|longcos|pantech|gionee|^sie-|portalmmm|";
		$regex_match.="jigs browser|hiptop|^ucweb|^benq|haier|^lct|operas*mobi|opera*mini|320x320|240x320|176x220";
		$regex_match.=")/i";		
		return preg_match($regex_match, strtolower($user_agent));
		
    }
    
    public function isLefttoRightWrite(){
    	return in_array(Mage::app()->getLocale()->getLocaleCode(), array('ar_DZ','ar_EG','ar_KW','ar_MA',
    																	 'ar_SA','he_IL','fa_IR'));    	
    }
    
    public function getGiftWrapTaxAmount(Mage_Sales_Model_Quote_Address $address, $amount){
    	
    	if (!intval($this->getConfigData('gift_wrapping/tax_class'))){
    		return $amount;
    	}    	
    	$calculation = Mage::getModel('tax/calculation'); 
    	$request = $calculation->getRateRequest($address, null, null, Mage::app()->getStore());    	
    	$request->setProductClassId(intval($this->getConfigData('gift_wrapping/tax_class')));
    	    	
    	$taxRate = $calculation->getRate($request);
    	
    	return ($amount + $calculation->calcTaxAmount($amount, $taxRate));    	
    }
    
    public function getCountriesStatesRequired(){
    	$result = array();
    	
    	if ($this->getConfigData('address_fields/region') == 'req'){
    		$country_collection = Mage::helper('directory')->getCountryCollection();
    		foreach ($country_collection as $country){
    			$result[] = $country->getCountryId();
    		}
    	}
    	return Mage::helper('core')->jsonEncode($result);
    	
    }
	
}
