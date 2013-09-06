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

function glcChangeDesign(){
	if ($('gomage_checkout_design')){
		if ($('gomage_checkout_design').visible()){
			$$('.glc-design-child-head, .glc-design-child').each(function(e){
				e.show();
			});
		}else{
			$$('.glc-design-child-head, .glc-design-child').each(function(e){
				e.hide();
			});
		}
	}
}

Event.observe(document, 'dom:loaded', function() {
		
	['gomage_checkout_skin_header-head', 'gomage_checkout_skin_block-head',
	 'gomage_checkout_skin_button-head', 'gomage_checkout_skin_popup-head'].each(function(e){
		 if ($(e)){
			 $(e).up('div').addClassName('glc-design-child-head');
		 }
	 });
	['gomage_checkout_skin_header', 'gomage_checkout_skin_block',
	 'gomage_checkout_skin_button', 'gomage_checkout_skin_popup'].each(function(e){
		 if ($(e)){
			 $(e).addClassName('glc-design-child');
		 }
	 });
	glcChangeDesign();
	
	Event.observe($('gomage_checkout_design-head'),'click', function(){
		glcChangeDesign();
	});
	
	
});
