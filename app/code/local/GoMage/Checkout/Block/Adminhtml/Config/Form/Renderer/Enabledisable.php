<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 5.9.2
 * @since        Class available since Release 2.0
 */

class GoMage_Checkout_Block_Adminhtml_Config_Form_Renderer_Enabledisable extends Mage_Adminhtml_Block_System_Config_Form_Field {
	
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		
		$websites = Mage::helper('gomage_checkout')->getAvailableWebsites();
		
		if (! empty($websites)) {
			
			$scope_website_code = $this->getRequest()->getParam('website');
			
			$scope_website = Mage::getModel('core/website')->load($this->getRequest()->getParam('website'), 'code');
			
			if ($scope_website && in_array($scope_website->getWebsiteId(), $websites)) {
				
				$html = $element->getElementHtml();
			
			}
			elseif (! $scope_website_code) {
				
				$html = $element->getElementHtml();
			
			}
			else {
				
				$html = '<strong class="required">' . $this->__('Please buy additional domains') . '</strong>';
			
			}
		
		}
		else {
			
			$html = '<strong class="required">' . $this->__('Please enter a valid key') . '</strong>';
		
		}
		
		return $html;
	
	}

}