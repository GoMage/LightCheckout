<?php
 /**
 * GoMage.com
 *
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 2.4
 */

class GoMage_Checkout_Model_Paypal_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{

	protected function prepareGiftWrap($request, $handlingamt = false)
    {
    	$gift_wrap_amount = 0;
        foreach (Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems() as $_item){
        	if ($_item->getBaseGomageGiftWrapAmount()){
        		$gift_wrap_amount += $_item->getBaseGomageGiftWrapAmount();
        	}
        }                                
        if ($gift_wrap_amount){
	        $request['GIFTWRAPENABLE'] = "1";
			$request['GIFTWRAPNAME'] =  Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/title');
			$request['GIFTWRAPAMOUNT'] = $this->_filterAmount($gift_wrap_amount);
			if ($handlingamt) {
				$request['HANDLINGAMT'] = $this->_filterAmount($gift_wrap_amount);
			}
        }    
    	return $request;
    }
    
    public function call($methodName, array $request)
    {    	
    	if (in_array($methodName, array(self::DO_EXPRESS_CHECKOUT_PAYMENT, self::SET_EXPRESS_CHECKOUT))){    		
    		$request = $this->prepareGiftWrap($request, $methodName == self::SET_EXPRESS_CHECKOUT);    		
    	}

    	return parent::call($methodName, $request);
    }
	
}
