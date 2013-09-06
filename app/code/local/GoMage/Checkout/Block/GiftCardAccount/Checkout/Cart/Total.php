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
 * @since        Class available since Release 4.0
 */

class GoMage_Checkout_Block_GiftCardAccount_Checkout_Cart_Total extends Enterprise_GiftCardAccount_Block_Checkout_Cart_Total {
	
	protected function _construct() {		
		if ($this->getRequest()->getRouteName() == 'gomage_checkout' && $this->getRequest()->getControllerName() == 'onepage') {
			$h = Mage::helper('gomage_checkout');
			if (( bool ) $h->getConfigData('general/enabled')) {
				$this->_template = 'gomage/checkout/giftcardaccount/cart/total.phtml';
			}
		}
		parent::_construct();
	}
}
