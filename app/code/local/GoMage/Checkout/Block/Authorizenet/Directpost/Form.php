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
 * @since        Class available since Release 2.4
 */

class GoMage_Checkout_Block_Authorizenet_Directpost_Form extends Mage_Authorizenet_Block_Directpost_Form {
	
	protected function _toHtml() {
		$payment = Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment();
		if (! $payment->getMethod()) {
			return null;
		}
		if ($this->getMethod()->getCode() != Mage::getSingleton('authorizenet/directpost')->getCode()) {
			return null;
		}
		
		return parent::_toHtml();
	}
	
	public function setMethodInfo() {
		$payment = Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment();
		if ($payment->getMethod()) {
			$this->setMethod($payment->getMethodInstance());
		}		
		return $this;
	}
}
   