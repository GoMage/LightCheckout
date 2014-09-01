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
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso,
            'no_span'      => 1,
        ));

        $element->setId('delivery_date');

        $date_value = time() + Mage::helper('gomage_deliverydate')->getDeliveryDayShift()*60*60*24;
        
        
        $element->setValue(date('d.m.Y', $date_value));

        $form->addElement($element, false);

        if (Mage::helper('gomage_checkout')->getConfigData('deliverydate/showtime')){
	        $values = array();
	
	        $delivery_days = Mage::helper('gomage_deliverydate')->getDeliveryDays();
	        if (isset($delivery_days[date('w', $date_value)])){
	            $values_options = Mage::getModel('gomage_deliverydate/adminhtml_system_config_source_hour')->toOptionHash();
	            foreach($delivery_days[date('w', $date_value)] as $value){
	                $values[$value] = $values_options[$value];
	            }
	        }
	
	        $form->addField('delivery_time', 'select', array(
	            'name'   => 'deliverydate[time]',
	            'title'  => $this->__('Delivery Time'),
	            'no_span'   => 1,
	            'values'	=> $values
	        ));
        }

        return $form->getElements();
    }

}