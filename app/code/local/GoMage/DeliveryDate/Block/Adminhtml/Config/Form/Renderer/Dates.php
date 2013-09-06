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

class GoMage_DeliveryDate_Block_Adminhtml_Config_Form_Renderer_Dates extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
    	
    	$html = '';
    	
    	$value = explode(',', $element->getValue());
    	
    	$form = $element->getForm();
    	
    	$nameprefix = $element->getName();
    	
        $element->setValues(array(
        		'all'		=> $this->__('All Days'),
        		'selected'	=> $this->__('Selected Days'),
        	))
        	->setOnchange('$(\'gomage-delivverydate-specdays\').style.display = (this.value == \'selected\' ? \'block\' : \'none\')')
            ->setName($nameprefix . '[]');
        
        $mode_value = array_shift($value);
        
        if(count($value)){
        	$element->setValue($mode_value);
        }
        
        $html .= $element->getElementHtml();
        
        $element = new Varien_Data_Form_Element_Multiselect();
        
        $element->setForm($form);
        $element->setId('gomage-delivverydate-specdays');
        $element->setClass('select');
        $element->setStyle('margin-top:10px;height:160px;'.($mode_value != 'selected' ? 'display:none;' : ''));
        $element->setName($nameprefix . '[]');
        $element->setValues(array(
        		array('value'=> 0, 'label' => $this->__('Sunday')),
        		array('value'=> 1, 'label' => $this->__('Monday')),
        		array('value'=> 2, 'label' => $this->__('Tuesday')),
        		array('value'=> 3, 'label' => $this->__('Wednesday')),
        		array('value'=> 4, 'label' => $this->__('Thursday')),
        		array('value'=> 5, 'label' => $this->__('Friday')),
        		array('value'=> 6, 'label' => $this->__('Saturday')),
        	));
        $element->setValue($value);
        $html .= $element->getElementHtml();
        
        return $html;
    }
}