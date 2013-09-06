<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.0
 * @since        Class available since Release 1.0
 */

class GoMage_Checkout_Block_Adminhtml_Address extends Mage_Adminhtml_Block_System_Config_Form_Field {
	
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		
		$html = parent::_getElementHtml($element);
		
		$html .= '<p style="margin-top:5px;"><input name="groups[address_fields][fields][city][value]" class="input-text" style="width:50px;margin-right:5px;" type="text"/><label>Sort order</label></p>';
		
		return $html;
	
	}

}