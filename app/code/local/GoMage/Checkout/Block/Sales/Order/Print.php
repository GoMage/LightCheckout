<?php

 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.7
 * @since        Class available since Release 2.5
 */

class GoMage_Checkout_Block_Sales_Order_Print extends Mage_Sales_Block_Order_Print
{
	protected function _toHtml()
    {
    	$helper = Mage::helper('gomage_checkout');
		if ($helper->getConfigData('general/enabled') && $helper->getConfigData('deliverydate/deliverydate')){						
        	$this->setTemplate('gomage/checkout/sales/order/print.phtml');
		}      	
        return parent::_toHtml();
    }

}

