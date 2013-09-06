<?php
 /**
 * GoMage.com
 *
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */
 	
	class GoMage_All_Block_Adminhtml_Sales_Order_View_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info{
		
		protected function _afterToHtml($html)
	    {
	    	
	    	if($this->getChild('gomage.order_info')){
	    	
	    	$html .= $this->getChild('gomage.order_info')->toHtml();
	    	
	    	}
	    	
	        return parent::_afterToHtml($html);
	    }
		
	}