<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2018 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 5.9.4
 * @since        Class available since Release 2.5
 */

class GoMage_Checkout_Block_Sales_Order_Info extends Mage_Sales_Block_Order_Info
{
    protected function _construct()
    {
        parent::_construct();
		$helper = Mage::helper('gomage_checkout');
		if ($helper->getConfigData('general/enabled') && $helper->getConfigData('deliverydate/deliverydate')){						
        	$this->setTemplate('gomage/checkout/sales/order/info.phtml');
		}        
    }
}
