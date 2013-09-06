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

class GoMage_Checkout_Block_Adminhtml_Sales_Order_View_Info_Block extends Mage_Core_Block_Template {
	
	protected $order;
	
	public function getOrder() {
		
		if (is_null($this->order)) {
			if (Mage::registry('current_order')) {
				$order = Mage::registry('current_order');
			}
			elseif (Mage::registry('order')) {
				$order = Mage::registry('order');
			}
			else {
				$order = new Varien_Object();
			}
			$this->order = $order;
		}
		return $this->order;
	}

}