<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 2.5
 */

class GoMage_DeliveryDate_Helper_Data extends Mage_Core_Helper_Abstract{
		
	protected $delivery_days = null;
	protected $nonworking_days = null;
	
	public function getDeliveryDays(){
		if(is_null($this->delivery_days)){
			$days = Mage::getStoreConfig('gomage_checkout/deliverydate/config');
			$this->delivery_days = array();
			if ($days){
				$days = @unserialize($days);
				if (is_array($days) && count($days)){
					foreach($days as $value){
						if ($value['available']){
							$time_range = $this->getTimeRange($value['time_from'], $value['time_to']);
							if (isset($this->delivery_days[$value['day']])){
								$this->delivery_days[$value['day']] = array_merge($this->delivery_days[$value['day']], $time_range);
								$this->delivery_days[$value['day']] = array_unique($this->delivery_days[$value['day']]);
							}else{
								$this->delivery_days[$value['day']] = $time_range;
							}							
						}
					}
				}
				
				foreach ($this->delivery_days as $key => $value){
					sort($value, SORT_NUMERIC);
					$this->delivery_days[$key] = $value;
				}
				
			}			 
		}
		
		return  $this->delivery_days;
	}
	
	public function getNonWorkingDays(){
		if(is_null($this->nonworking_days)){
			$days = Mage::getStoreConfig('gomage_checkout/deliverydate/nonworking');
			$this->nonworking_days = array();
			if ($days){
				$this->nonworking_days = @unserialize($days);
			}			 
		}		
		return  $this->nonworking_days;
	}
	
	public function getTimeRange($from, $to){
				
		if (!$to) $to = 24;
		
		$hours = array();
		if ($from > $to)
		{
		    for($i = $from; $i <= 23; $i++)
		        $hours[] = $i;
		    for($i = 0; $i <= $to; $i++)
		        $hours[] = $i;    
		}           			         
		else
		{
		    for($i = $from; $i <= $to; $i++)
		    {
		        if ($i == 24)
		            $hours[] = 0;
		        else
		            $hours[] = $i;
		    }        
		}

		return $hours;		
	}
	
	public function isEnableDeliveryDate(){
		return (Mage::getStoreConfig('gomage_checkout/deliverydate/deliverydate') &&
				count($this->getDeliveryDays()));
	}
	
	public function getShippingMethods(){
		return explode(',', Mage::getStoreConfig('gomage_checkout/deliverydate/shipping_methods'));
	}
    	
}
