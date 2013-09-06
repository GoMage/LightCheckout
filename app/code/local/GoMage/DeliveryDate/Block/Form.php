<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.4
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
		
		public function getFields(){
        
	        $form = new Varien_Data_Form();
	        
	        //todo add logic for getting fields by step    
	        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
	        
	        switch (intval(Mage::helper('gomage_checkout')->getConfigData('deliverydate/dateformat')))
	        {
	            case GoMage_DeliveryDate_Model_Adminhtml_System_Config_Source_Dateformat::EUROPEAN:
                    $dateFormatIso = 'dd.MM.yyyy';
	            break;   
	            default:
	                $dateFormatIso = 'MM.dd.yyyy'; 
	        }
	        
	        
	        
	        $element = new GoMage_DeliveryDate_Model_Form_Element_Date(array(
	            'name'   => 'deliverydate[date]',
	            'label'  => $this->__('Select a Date:'),
	            'title'  => $this->__('Delivery Date'),
	            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
	            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
	            'format'       => $dateFormatIso,
	            'no_span'      => 1,
	        ));
	        
	        $element->setId('delivery_date');
	        
	        $interval = intval(Mage::helper('gomage_checkout')->getConfigData('deliverydate/interval_days'));
	        $available_hour_from = Mage::helper('gomage_checkout')->getConfigData('deliverydate/available_hour_from');
	        $available_hour_to = Mage::helper('gomage_checkout')->getConfigData('deliverydate/available_hour_to');
	        
	        
	        if($interval == 0 && Mage::app()->getLocale()->date()->toString('H')>$available_hour_to){
	        	$interval++;
	        }
	        
	        $available_days = explode(',', Mage::helper('gomage_checkout')->getConfigData('deliverydate/available_days'));
	        $shift = 0;
	        if(in_array('selected', $available_days)){
	        
	        unset($available_days[array_search('selected', $available_days)]);
	        
	        $w = date('w', time()+($interval*60*60*24));
	        
	        if(!in_array($w, $available_days)){
	        	
	        	if($w > max($available_days)){
	        		$shift = 7-$w + min($available_days);
	        		
	        	}else{
	        		foreach($available_days as $d){
	        			if($d > $w){
	        				$shift = $d-$w;
	        				break;
	        			}
	        		}
	        	}
	        }
	        }
	        	        		    	        	        
	        $element->setValue(date('d.m.Y', time()+(($interval+$shift)*(60*60*24))));
	        
	        $form->addElement($element, false);
	        
	        $values = array();
	        
	        
	        foreach(Mage::getModel('gomage_deliverydate/adminhtml_system_config_source_hour')->toOptionArray() as $option){
	        	
	        	if($option['value'] >= $available_hour_from && $option['value'] <= $available_hour_to){
	        		$values[$option['value']] = $option['label'];
	        	}
	        	
	        }
	        
	        $form->addField('delivery_time', 'select', array(
	            'name'   => 'deliverydate[time]',
	            'title'  => $this->__('Delivery Time'),
	            'no_span'   => 1,
	            'values'	=> $values
	        ));
	        	        
	        return $form->getElements();
	    }
		
	}