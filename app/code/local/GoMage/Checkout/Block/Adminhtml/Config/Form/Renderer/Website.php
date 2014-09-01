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
 		
class GoMage_Checkout_Block_Adminhtml_Config_Form_Renderer_Website extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
    	
    	$html = '';
    	
    	$r = Mage::getStoreConfig('gomage_activation/lightcheckout/ar');
    	
    	$value = explode(',', str_replace($r, '', Mage::helper('core')->decrypt($element->getValue())));
    	
    	$nameprefix = $element->getName();
    	$idprefix = $element->getId();
    	
    	$element->setName($nameprefix . '[]');
    	
    	
    	$info = Mage::helper('gomage_checkout')->ga();
        
        if(isset($info['d']) && isset($info['c']) && intval($info['c']) > 0){
    	
    	
    	foreach (Mage::app()->getWebsites() as $website) {
    		
    		$element->setChecked(false);
    		
            $id = $website->getId();
            $name = $website->getName();
            
            $element->setId($idprefix.'_'.$id);
            $element->setValue($id);
            $element->setClass('gomage-checkout-available-sites');
            
            if(in_array($id, $value) !== false){
            	$element->setChecked(true);
            }
            
            if ($id!=0) {
            	$html .= '<div><label>'.$element->getElementHtml().' '.$name.' </label></div>';
            }
        }
        
        
        
        $html .= '
        	<input id="'.$idprefix.'_diasbled" type="hidden" disabled="disabled" name="'.$nameprefix.'" />
        	<script type="text/javascript">
        	
        	function updateGomageCheckoutWebsites(){
        		
        		$("'.$idprefix.'_diasbled").disabled = "disabled";
        		
        		if($$(".gomage-checkout-available-sites:checked").length >= '.intval($info['c']).'){
    				$$(".gomage-checkout-available-sites").each(function(e){
    					if(!e.checked){
    						e.disabled = "disabled";
    					}
    				});
    				
    			}else {
    				$$(".gomage-checkout-available-sites").each(function(e){
    					if(!e.checked){
    						e.disabled = "";
    					}
    				});
    				if($$(".gomage-checkout-available-sites:checked").length == 0){
    				
    					$("'.$idprefix.'_diasbled").disabled = "";
    				
    				}
    				
    			}
        	}
        	
        	$$(".gomage-checkout-available-sites").each(function(e){
        		e.observe("click", function(){
        			updateGomageCheckoutWebsites();
        		});
        	});
        	
        	updateGomageCheckoutWebsites();
        	
        </script>';
    	
    	}else{
    		$html = sprintf('<strong class="required">%s</strong>', $this->__('Please enter a valid key'));
    	}
    	
    	return $html;
    	
    }
}