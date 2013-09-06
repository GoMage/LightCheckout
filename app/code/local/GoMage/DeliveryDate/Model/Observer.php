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
	
	class GoMage_DeliveryDate_Model_Observer{
		
		public function coreBlockAbstractPrepareLayoutBefore($event){
			switch($event->getBlock()->getNameInLayout()):
			case ('sales_order.grid'):
				if(Mage::getVersion() >= '1.4.0'){
				
					$event->getBlock()->addColumnAfter('gomage_deliverydate', array(
			            'header' => Mage::helper('sales')->__('Delivery Date'),
			            'type' => 'datetime',
			            'index' => 'gomage_deliverydate',
			            'width' => '160px',
			        ), 'grand_total');
			        
		    	}else{
		    		
		    		$event->getBlock()->addColumn('gomage_deliverydate', array(
			            'header' => Mage::helper('sales')->__('Delivery Date'),
			            'type' => 'datetime',
			            'index' => 'gomage_deliverydate',
			            'width' => '160px',
			        ));
		    		
		    	}
			break;
			endswitch;
		}
		
		
		
		public function saveQuoteData($event){
			
			$request	= Mage::app()->getRequest();
			$quote		= $event->getQuote();
			
			if(($deliverydate = $request->getPost('deliverydate', false)) && $quote){
				
				if(is_array($deliverydate)){
					
					
					if($deliverydate['when'] == 'later'){
						
						$matches = array();
						
						preg_match_all('/(\d{2})\.(\d{2})\.(\d{4})/', strval($deliverydate['date']), $matches);
						
						if(!empty($matches) && count($matches) == 4){
							
							$date = array(
								'y'=>$matches[3][0],
								'm'=>$matches[1][0],
								'd'=>$matches[2][0],
							);
							$mysql_date = implode('', $date);
							if(isset($deliverydate['time'])){
								$time = explode(':', strval($deliverydate['time']));
								if(count($time) == 2){
									$mysql_time = sprintf('%02d%02d00', $time[0], $time[1]);
								}else{
									$mysql_time = '000000';
								}
							}else{
								$mysql_time = '000000';
							}
							
							$value = date('YmdHis', (strtotime($mysql_date.$mysql_time)+intval(@$deliverydate['customer_offset'])));
							
							if($value){
								
								$quote->setData('gomage_deliverydate', $value);
								
								$formated_value = Mage::app()->getLocale()->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString(Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM));
								
								$quote->setData('gomage_deliverydate_formated', $formated_value);
								
							}
						}
						
					}else{
						
						$quote->setData('gomage_deliverydate', null);
						
					}
				}
			}
			
		}
		
	}