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

	
	class GoMage_Checkout_Block_Onepage_Review extends GoMage_Checkout_Block_Onepage_Abstract{
		
		
		
		public function getCustomerComment(){
			
			return strval($this->getCheckout()->getQuote()->getGomageCheckoutCustomerComment());
		}
		
	}