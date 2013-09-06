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

class GoMage_Checkout_Block_Onepage extends GoMage_Checkout_Block_Onepage_Abstract {
	
	public function getContent() {
		return nl2br($this->helper->getConfigData('general/content'));
	}
	
	public function getLoginForm() {
		return $this->getLayout()->createBlock('core/template')->setTemplate('gomage/checkout/onepage/login.phtml')->toHtml();
	}
	
	public function getContentCssClasses() {
		$classes = array();
		if (Mage::getSingleton('checkout/session')->getQuote()->isVirtual()) {
			$classes[] = 'not_shipping_mode';
		}
		
		if (! Mage::helper('gomage_deliverydate')->isEnableDeliveryDate()) {
			$classes[] = 'not_deliverydate_mode';
		}
		
		if (! Mage::getSingleton('checkout/session')->getShippingSameAsBilling()) {
			
			if (! Mage::getStoreConfig('gomage_checkout/deliverydate/deliverydate')) {
				$classes[] = 'notddate_diferent_shipping_address';
			}
			else {
				$classes[] = 'diferent_shipping_address';
			}
		
		}
		
		if (Mage::helper('gomage_checkout')->isLefttoRightWrite()){
			$classes[] = 'glc-rtl';
		} 
		
		return implode(' ', $classes);
		
	}
	
	

}