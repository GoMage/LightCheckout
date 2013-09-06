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
	
	class GoMage_DeliveryDate_Block_Form extends GoMage_Checkout_Block_Onepage_Abstract{
		
		
		protected $date;
		public function getDate(){
			
			if(is_null($this->date)){
				$this->date = $this->getCheckout()->getQuote()->getGomageDeliverydate();
			}
			return $this->date;
		}
		
	}