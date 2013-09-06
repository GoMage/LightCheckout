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
 * @since        Class available since Release 3.1
 */

if (Mage::helper('gomage_klarnapayment')->isGoMage_KlarnaPaymentEnabled()) {
	abstract class GoMage_KlarnaPayment_Block_Adminhtml_Sales_Order_View_InfoAbstract extends Klarna_KlarnaPaymentModule_Block_Adminhtml_Sales_Order_View_Info {
		
		protected function _afterToHtml($html) {			
			if ($this->getChild('gomage.checkout.order.info')) {				
				$html .= $this->getChild('gomage.checkout.order.info')->toHtml();			
			}			
			return parent::_afterToHtml($html);
		}
		
	}
}
else {
	abstract class GoMage_KlarnaPayment_Block_Adminhtml_Sales_Order_View_InfoAbstract extends GoMage_Checkout_Block_Adminhtml_Sales_Order_View_Info {
	
	}
}

class GoMage_KlarnaPayment_Block_Adminhtml_Sales_Order_View_Info extends GoMage_KlarnaPayment_Block_Adminhtml_Sales_Order_View_InfoAbstract {

}