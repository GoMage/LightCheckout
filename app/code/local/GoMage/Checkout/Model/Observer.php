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
	
	class GoMage_Checkout_Model_Observer {
		
		static public function salesOrderLoad($event){
			
			
			if($date = $event->getOrder()->getGomageDeliverydate()){
				
				$formated_date = Mage::app()->getLocale()->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString(Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM));
				$event->getOrder()->setGomageDeliverydateFormated($formated_date);
			};
			
		}
		
	}