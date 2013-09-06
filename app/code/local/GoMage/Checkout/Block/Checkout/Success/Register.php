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
 * @since        Class available since Release 5.0
 */

class GoMage_Checkout_Block_Checkout_Success_Register extends Mage_Customer_Block_Form_Register {
	
	protected function _prepareLayout() {
		$h = $this->helper('gomage_checkout');
		if ($h->getConfigData('general/enabled') && ! $this->helper('customer')->isLoggedIn() && $h->getConfigData('registration/success_page')) {
			$this->setTemplate('customer/form/register.phtml');
			
			$this->setSuccessUrl($this->getUrl('customer/account'));
			
			$order = $this->getOrder();
			$form_data = $order->getBillingAddress()->getData(); 
			Mage::getSingleton('customer/session')->setCustomerFormData($form_data);
		}
	}
	
	public function getOrder() {
		return Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
	}

}

