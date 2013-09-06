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
 * @since        Class available since Release 2.5
 */

class GoMage_Checkout_Block_Checkout_Success_Summary extends Mage_Sales_Block_Order_Print {
	protected function _prepareLayout() {
		$h = Mage::helper('gomage_checkout');
		if ($h->getConfigData('general/order_summary') && $h->getConfigData('general/enabled')) {
			$this->setTemplate('gomage/checkout/success/summary.phtml');
		}
	}
	
	public function getOrder() {		
		return Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
	}

}

