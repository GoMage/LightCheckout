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
 * @since        Class available since Release 3.1
 */

if(Mage::helper('gomage_klarnapayment')->isGoMage_KlarnaPaymentEnabled()) {	
	abstract class GoMage_KlarnaPayment_Block_Adminhtml_Sales_Order_Invoice_TotalsAbstract extends Klarna_KlarnaPaymentModule_Block_Adminhtml_Sales_Order_Totals{		
		protected function _initTotals()
	    {        
	        parent::_initTotals();
	        
	        $add_giftwrap = false;
	        $items = $this->getSource()->getAllItems();                
	        foreach ($items as $item) {
	            if ($item->getData('gomage_gift_wrap')) {
	                $add_giftwrap = true;
	                break;
	            }
	        }            
	        if ($add_giftwrap){                
	                $this->_totals['gomage_gift_wrap'] = new Varien_Object(array(
	                    'code'      => 'gomage_gift_wrap',
	                    'value'     => $this->getSource()->getGomageGiftWrapAmount(),
	                    'base_value'=> $this->getSource()->getBaseGomageGiftWrapAmount(),
	                    'label'     => Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/title')
	                ));
	        }	        
	        return $this;
	    } 		
	}		 	
}else {
	abstract class GoMage_KlarnaPayment_Block_Adminhtml_Sales_Order_Invoice_TotalsAbstract extends GoMage_Checkout_Block_Adminhtml_Sales_Order_Invoice_Totals{
		
	}
}

class GoMage_KlarnaPayment_Block_Adminhtml_Sales_Order_Invoice_Totals extends GoMage_KlarnaPayment_Block_Adminhtml_Sales_Order_Invoice_TotalsAbstract{

}