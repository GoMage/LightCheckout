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
 * @since        Class available since Release 4.2
 */

class GoMage_Checkout_Block_Adminhtml_Sales_Items_Column_Name extends Mage_Adminhtml_Block_Sales_Items_Column_Name {
	
	public function getOrderOptions() {
		$result = parent::getOrderOptions();
		if ($item = $this->getItem()) {
			if ($item->getData('gomage_gift_wrap')) {
				$title = Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/title');
				$result[] = array("value" => $this->__("Yes"), "label" => $title);
			}
		}
		return $result;
	}
}
?>
