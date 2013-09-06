<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.1
 * @since        Class available since Release 5.1
 */

class GoMage_Checkout_Model_Observer_Notify {
	
	public function notify($event) {		
		if (Mage::getSingleton('admin/session')->isLoggedIn() && Mage::getStoreConfig('gomage_notification/notification/enable')) {			
			Mage::helper('gomage_checkout')->notify();		
		}
	}

}