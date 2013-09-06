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

class GoMage_Checkout_Block_Adminhtml_Fieldset extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {
	
	public function render(Varien_Data_Form_Element_Abstract $element) {
		$html = $this->_getHeaderHtml($element);
		
		foreach ($element->getSortedElements() as $field) {
			$html .= '<div class="address-field">' . $field->toHtml() . '</div>';
		}
		
		$html .= $this->_getFooterHtml($element);
		
		return $html;
	}

}