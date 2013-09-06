<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.4
 * @since        Class available since Release 1.0
 */

class GoMage_Checkout_Model_Quote_Address extends Mage_Sales_Model_Quote_Address
{
	
	protected $required_fields = array();
	
	public function __construct(){
		parent::__construct();
		
		$this->required_fields = array(
			'firstname'		=>array('NotEmpty', 'Please enter the first name.'),
			'lastname'		=>array('NotEmpty', 'Please enter the last name.'),
			'street'		=>array('NotEmpty', 'Please enter the street.'),
			'city'			=>array('NotEmpty', 'Please enter the city.'),
			'telephone'		=>array('NotEmpty', 'Please enter the telephone number.'),
			'postcode'		=>array('NotEmpty', 'Please enter the zip/postal code.'),
			'country_id'	=>array('NotEmpty', 'Please enter the country.'),
			'region'		=>array('NotEmpty', 'Please enter the state/province.'),
		);
		
		foreach(Mage::getStoreConfig('gomage_checkout/address_fields') as $field_name=>$status){
			
			if($status != 'req' && isset($this->required_fields[$field_name])){
				unset($this->required_fields[$field_name]);
			}
			
		}
		
		
	}
	
	public function validate(){
		
		if((bool)Mage::helper('gomage_checkout')->getConfigData('general/enabled')){
		
        $errors = array();
        $helper = Mage::helper('customer');
        $this->implodeStreetAddress();
        
        foreach($this->required_fields as $fieldName=>$method){
        	
        	if($fieldName == 'region' && intval(Mage::getStoreConfig('gomage_checkout/address_fields/country_id'))>0){
        		
        		if ($this->getCountryModel()->getRegionCollection()->getSize()
		               && !Zend_Validate::is($this->getRegionId(), 'NotEmpty')) {
		            $errors[] = $helper->__('Please enter the state/province.');
		        }
        	
        	}elseif($fieldName == 'postcode' && Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 4) ){
        		
        		
        		$_havingOptionalZip = Mage::helper('directory')->getCountriesWithOptionalZip();
		        if (!in_array($this->getCountryId(), $_havingOptionalZip) && !Zend_Validate::is($this->getPostcode(), 'NotEmpty')) {
		            $errors[] = $helper->__('Please enter the zip/postal code.');
		        }
        		
        	}else{
	        	
	        	if (!Zend_Validate::is($this->getData($fieldName), $method[0])) {
		            $errors[] = $helper->__($method[1]);
		        }
		        
	        }
        	
        }
        
        if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }
        return $errors;
        
        }else{
        	return parent::validate();
        }
    }
}
