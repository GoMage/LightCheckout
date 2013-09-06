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
 * @since        Class available since Release 2.0
 */

class GoMage_DeliveryDate_Model_Form_Element_Date extends Varien_Data_Form_Element_Date
{
    
    public function getElementHtml()
    {
        $this->addClass('input-text');

        $html = sprintf(
            '<input name="%s" id="%s" readonly="readonly" value="%s" %s style="width:65px !important;border:0 !important;background:none !important;font-weight:bold !important;" />'
            .' <img src="%s" alt="" class="v-middle" id="%s_trig" title="%s" style="%s" />',
            $this->getName(), $this->getHtmlId(), $this->_escape($this->getValue()), $this->serialize($this->getHtmlAttributes()),
            $this->getImage(), $this->getHtmlId(), Mage::helper('gomage_checkout')->__('Select Date'), ($this->getDisabled() ? 'display:none;' : '')
        );
        $outputFormat = $this->getFormat();
                                
        if (empty($outputFormat)) {
            throw new Exception('Output format is not specified. Please, specify "format" key in constructor, or set it using setFormat().');
        }
        $displayFormat = Varien_Date::convertZendToStrFtime($outputFormat, true, (bool)$this->getTime());
        		
		$available_days = explode(',', Mage::helper('gomage_checkout')->getConfigData('deliverydate/available_days'));
		
		$interval = intval(Mage::helper('gomage_checkout')->getConfigData('deliverydate/interval_days'));
		$available_hour_to = Mage::helper('gomage_checkout')->getConfigData('deliverydate/available_hour_to');
		
		if($interval == 0 && Mage::app()->getLocale()->date()->toString('H')>$available_hour_to){
        	$interval++;
        }
		
		$mode = array_shift($available_days);
		
		$disabled_dates_conf = '';
		
		if($mode == 'selected'){
			$disabled_dates = array_diff(array(0,1,2,3,4,5,6), $available_days);
			
			if(!empty($disabled_dates)){
			
				$_disabled_dates_conf = array();
				
				foreach($disabled_dates as $day){
				
					$_disabled_dates_conf[] = sprintf('date.getDay() == %d', $day);
				
				}
			}
			
			$disabled_dates_conf = 'disabled: function(date) {
                    	
				        if ('.implode('||', $_disabled_dates_conf).') {
				            return true;
				        } else {
				            return false;
				        }
				    }';
			
		}
		
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
                    onSelect   : function() { this.hide() }
                });
                
    			}
    			
    			initDeliveryDateCallendar();
            //]]>
            </script>',
            $this->getHtmlId(), $displayFormat,
            $this->getTime() ? 'true' : 'false', $this->getHtmlId()
        );

        $html .= $this->getAfterElementHtml();

        return $html;
    }
}
