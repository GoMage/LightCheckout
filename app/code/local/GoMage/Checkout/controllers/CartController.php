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
 * @since        Class available since Release 2.0
 */ 

require_once(Mage::getBaseDir('app').'/code/core/Mage/Checkout/controllers/CartController.php');

class GoMage_Checkout_CartController extends Mage_Checkout_CartController
{
	public function indexAction(){
		
	    $h = Mage::helper('gomage_checkout');
	    
		if($h->getConfigData('general/disable_cart') && $h->getConfigData('general/enabled')){
			
			$quote = Mage::getSingleton('gomage_checkout/type_onestep')->getQuote();
			
			if ($quote->hasItems() && !$quote->getHasError()) {
				if ($quote->validateMinimumAmount()) {
	            $this->_redirect('*/onepage');
	            }
	            return;
	        }
			
		}
		
		return parent::indexAction();
		
	}
}
