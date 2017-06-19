<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 5.9.2
 * @since        Class available since Release 2.2
 */
	
class GoMage_SagePay_Model_Observer{
	
	
	static public function checkoutEventAfterSave($event){
		
	    if(Mage::helper('gomage_sagepay')->isGoMage_SagePayEnabled())
	    {
    	    $_checkout = Mage::getSingleton('checkout/session');    
    	    $_checkout->setCustomerAssignedQuote(false);
    		$_checkout->setCustomerAdressLoaded(false);
	    }
		
	}
	
}