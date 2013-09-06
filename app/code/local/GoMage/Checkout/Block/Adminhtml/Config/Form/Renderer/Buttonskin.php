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
 		
class GoMage_Checkout_Block_Adminhtml_Config_Form_Renderer_Buttonskin extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
    	
    	$html = $element->getElementHtml();
    	
    	$html .= '<script type="text/javascript">$("gomage_checkout_general_skin").observe("change", function(){if(this.value == "default"){$("row_gomage_checkout_general_button_skin").show();}else{$("row_gomage_checkout_general_button_skin").hide();}});</script>';
    	
    	if(Mage::getStoreConfig('gomage_checkout/general/skin') !== 'default'){
    	
    	$html .= '<script type="text/javascript">$("row_gomage_checkout_general_button_skin").hide();</script>';
    	
    	}
    	
    	return $html;
    	
    }
}