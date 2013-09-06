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

class GoMage_Checkout_Model_Url_Rewrite_Request extends Mage_Core_Model_Url_Rewrite_Request {
	
	protected function _rewriteConfig() {

		$h = Mage::helper('gomage_checkout');
		
		if ($h->getConfigData('general/enabled')) {
			
			$requestPath = trim($this->_request->getPathInfo(), '/');
			
			if ($requestPath == 'checkout/onepage' || $requestPath == 'checkout/onepage/index') {
				if ($h->isAvailableWebsite() && $h->isCompatibleDevice()) {
					$this->_request->setPathInfo('gomage_checkout/onepage');
					return true;
				}
			}
		}
		
		return parent::_rewriteConfig();
	
	}

}
