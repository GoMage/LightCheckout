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
 * @since        Class available since Release 2.0
 */

class GoMage_DeliveryDate_Model_Form_Element_Date extends Varien_Data_Form_Element_Date
{
    
    public function getElementHtml()
    {
        $this->addClass('input-text');

        $html = sprintf(
            '<input name="%s" id="%s" readonly="readonly" value="%s" %s />' .
            '<span class="glc-ico ico-cal" id="%s_trig"></span>',
            $this->getName(), $this->getHtmlId(), $this->_escape($this->getValue()), $this->serialize($this->getHtmlAttributes()),
            $this->getHtmlId()
        );
        $outputFormat = $this->getFormat();
                                
        if (empty($outputFormat)) {
            throw new Exception('Output format is not specified. Please, specify "format" key in constructor, or set it using setFormat().');
        }
        $displayFormat = Varien_Date::convertZendToStrFtime($outputFormat, true, (bool)$this->getTime());
        		
		$available_days = Mage::helper('gomage_deliverydate')->getDeliveryDays();
		$available_days = array_keys($available_days);
		
		$interval = intval(Mage::helper('gomage_checkout')->getConfigData('deliverydate/interval_days'));
				
		$disabled_dates_conf = '';
		$_disabled_dates_conf = array('false');
				
		$disabled_dates = array_diff(array(0,1,2,3,4,5,6), $available_days);
					
		if(!empty($disabled_dates)){
										
			foreach($disabled_dates as $day){
				
				$_disabled_dates_conf[] = sprintf('(date.getDay() == %d)', $day);
				
			}
		}
		
		$nonworking_days = Mage::helper('gomage_deliverydate')->getNonWorkingDays();		
		foreach ($nonworking_days as $_value){			
			$_disabled_dates_conf[] = sprintf('(date.getDate() == %d && date.getMonth() == %d)', $_value['day'], $_value['month']);
		} 
		
		$shift = Mage::helper('gomage_deliverydate')->getDeliveryDayShift();		
		for($i=0; $i < $shift; $i++){
			$date = time() + $i*60*60*24;
			$_disabled_dates_conf[] = sprintf('(date.getDate() == %d && date.getMonth() == %d)', intval(date('d', $date)), intval(date('m', $date)) - 1);
		}
			
		$disabled_dates_conf = 'disabled: function(date) {
		
				        if ('.implode('||', $_disabled_dates_conf).') {
				            return true;
				        } else {
				            return false;
				        }
				    }';
			
		
		
        switch (intval(Mage::helper('gomage_checkout')->getConfigData('deliverydate/dateformat')))
        {
            case GoMage_DeliveryDate_Model_Adminhtml_System_Config_Source_Dateformat::EUROPEAN:
                $value_format = 'd.m.Y';
            break;   
            default:
                $value_format = 'm.d.Y'; 
        }
		
        $html .= sprintf('
            <script type="text/javascript">
            //<![CDATA[
            	
            	function initDeliveryDateCallendar(){
            	
                LightCheckoutCalendar.setup({
                	fdow:0,
                    inputField : "%s",
                    dateFormat : "%s",
                    showsTime: %s,
                    trigger: "%s_trig",
                    align: "Bl",
                    bottomBar: false,
                    min:"'.date($value_format, time()+($interval*60*60*24)).'",
                    singleClick : true'.($disabled_dates_conf ? ','.$disabled_dates_conf : '').',
                    onSelect   : function() { this.hide() },
                    addelClass : "'.(Mage::helper('gomage_checkout')->isLefttoRightWrite() ? "glc-rtl" : "").'"
                });
                
    			}
    			initDeliveryDateCallendar();
    			
    			var glc_delivery_days = ' . Zend_Json::encode(Mage::helper('gomage_deliverydate')->getDeliveryDays()) . ';
    			var glc_time_values = ' . Zend_Json::encode(Mage::getModel('gomage_deliverydate/adminhtml_system_config_source_hour')->toOptionHash()) . ';
    			
            //]]>
            </script>',
            $this->getHtmlId(), $displayFormat,
            $this->getTime() ? 'true' : 'false', $this->getHtmlId()
        );

        $html .= $this->getAfterElementHtml();

        return $html;
    }
}
