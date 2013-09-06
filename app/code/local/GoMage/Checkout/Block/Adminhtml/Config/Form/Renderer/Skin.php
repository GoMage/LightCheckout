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
 * @since        Class available since Release 5.0
 */

class GoMage_Checkout_Block_Adminhtml_Config_Form_Renderer_Skin extends Mage_Adminhtml_Block_System_Config_Form_Field {
	
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		
		$html = parent::_getElementHtml($element);
		
		$skin_config_model = Mage::getModel('gomage_checkout/adminhtml_system_config_source_skin_config');
		$skin_config = json_encode($skin_config_model->toOptionArray());
		$skin_config_default = json_encode($skin_config_model->toOptionDefaultArray());
		
		$javaScript = "
            <script type=\"text/javascript\">
            	var glc_skin_config = {$skin_config};   
            	var glc_skin_config_default = {$skin_config_default};            
                Event.observe('{$element->getHtmlId()}', 'change', function(){
                	
                    var skin = $('{$element->getHtmlId()}').value;
                    if (glc_skin_config.hasOwnProperty(skin)){
                    	var skin_config = glc_skin_config[skin];                    	
                    }else{
                    	var skin_config = glc_skin_config_default;	
					}                    
                    for (section in skin_config){
						for (field in skin_config[section]){
							if ($('gomage_checkout_' + section + '_' + field)){
								$('gomage_checkout_' + section + '_' + field).value = skin_config[section][field];
								if (field.indexOf('_color') > 0){									
									if (skin_config[section][field]){
                                        if (typeof $('gomage_checkout_' + section + '_' + field).color != 'undefined'){
										    $('gomage_checkout_' + section + '_' + field).color.fromString(skin_config[section][field]);
                                        }
									}else{
                                        if (typeof $('gomage_checkout_' + section + '_' + field).color != 'undefined'){
										    $('gomage_checkout_' + section + '_' + field).color.fromString('#FFFFFF');
                                        }
										$('gomage_checkout_' + section + '_' + field).value = '';
									}
								}								
							}
						}
					} 
                });
                
                Event.observe(document, 'dom:loaded', function() {
	                for (section in glc_skin_config_default){
							for (field in glc_skin_config_default[section]){
								if ($('gomage_checkout_' + section + '_' + field)){
									$('gomage_checkout_' + section + '_' + field).observe('change', function() {
				                        $('gomage_checkout_design_skin').value = 'default';
				                    });
								}
							}
					}
				});
				
            </script>";
		
		$html .= $javaScript;
		return $html;
	}

}