<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.0
 * @since        Class available since Release 1.0
 */

class GoMage_Checkout_Model_Url_Rewrite extends Mage_Core_Model_Url_Rewrite {
	
	public function rewrite(Zend_Controller_Request_Http $request = null, Zend_Controller_Response_Http $response = null) {
		
		$h = Mage::helper('gomage_checkout');
		
		if ($h->getConfigData('general/enabled')) {
			
			if (is_null($request)) {
				$request = Mage::app()->getFrontController()->getRequest();
			}
			$requestPath = trim($request->getPathInfo(), '/');
			
			if ($requestPath == 'checkout/onepage' || $requestPath == 'checkout/onepage/index') {
				if ($h->isAvailableWebsite() && $h->isCompatibleDevice()) {					
					$request->setAlias(self::REWRITE_REQUEST_PATH_ALIAS, $this->getRequestPath());
					$request->setPathInfo('gomage_checkout/onepage');
					return true;					
				}
			}		
		}
		
		parent::rewrite($request, $response);
	
	}

}