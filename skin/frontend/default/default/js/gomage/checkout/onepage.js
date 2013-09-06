var loadinfoTimeOut = null;
var checkedShipping = null;
var addessMode 		= 'merge';

jQuery(document).ready(function($){
	
	$.validationEngineLanguage.newLang();
    $('#gcheckout-onepage-form').validationEngine();
	
	
	var loadinfoTimer   = null;
	
	$('#gcheckout-onepage-form').ajaxForm({
		success:function(responseText, statusText){
			try{
				eval('var response = '+responseText);
			}catch(e){
				var response = new Object();
				response.error = true;
				response.message = 'Unknow error.';
			}
			if(response.error){
				
				//alert(response.message);
				
			}else{
				
				if(response.section == 'methods'){
					
					if(response.rates){
						$('#gcheckout-shipping-method-available').empty().append(response.rates);
					}
					
					
					var paymentsList = $('#checkout-payment-method-load');
					
					if(paymentsList.length){
						paymentsList.replaceWith(response.payments);
					}else{
						$('#gcheckout-payment-methods').append(response.payments);
					}
					
					$('#gcheckout-onepage-review div.totals').empty().append(response.totals);
					
					if(default_shipping_method && (input = $('#gcheckout-shipping-method-available input[value='+default_shipping_method+']')).get(0)){
						
						$(input).attr('checked', 'checked').trigger('click').trigger('change');
						
					}
					if(default_payment_method && (input = $('#gcheckout-payment-methods input[value='+default_payment_method+']').get(0))){
						
						$(input).attr('checked', 'checked').trigger('click').trigger('change');
						
					}
					
				}else if(response.section == 'payment_methods'){
					
					var paymentsList = $('#checkout-payment-method-load');
					
					if(paymentsList.length){
						paymentsList.replaceWith(response.payments);
					}else{
						$('#gcheckout-payment-methods').append(response.payments);
					}
					
					$('#gcheckout-onepage-review div.totals').empty().append(response.totals);
					
					if(default_payment_method && (input = $('#gcheckout-payment-methods input[value='+default_payment_method+']').get(0))){
						$(input).attr('checked', 'checked').trigger('click').trigger('change');
					}
					
				}else if(response.section == 'shiping_rates'){
					
					if(response.rates){
						$('#gcheckout-shipping-method-available').empty().append(response.rates);
					}
					$('#gcheckout-onepage-review div.totals').empty().append(response.totals);
					
					
					
					if(default_shipping_method && (input = $('#gcheckout-shipping-method-available input[value='+default_shipping_method+']')).get(0)){
						
						$(input).attr('checked', 'checked').trigger('click').trigger('change');
						
					}
					
				}else if(response.section == 'totals'){
					
					$('#gcheckout-onepage-review div.totals').empty().append(response.totals);
					
				}
				
			}
			
			hideLoadinfo();
			
		}
	});
	
	$('#gcheckout-login-form').ajaxForm({
		beforeSubmit:function(){
			$('#gcheckout-login-form .actions button').css({display:'none'});
			$('#gcheckout-login-form .actions .loadinfo').css({display:'block'});
		},
		success:function(responseText, statusText){
			try{
				eval('var response = '+responseText);
			}catch(e){
				var response = new Object();
				response.error = true;
				response.message = 'Unknow error.';
			}
			if(!response.error){
				location.reload();
			}else{
				if($('#gcheckout-login-form div.error').length == 0){
					$('#gcheckout-login-form').prepend('<div class="error"></div>');
				}
				
				$('#gcheckout-login-form div.error').empty().append(response.message);
				$('#gcheckout-login-form .actions button').css({display:'block'});
				$('#gcheckout-login-form .actions .loadinfo').css({display:'none'});
			}
			
		}
	});
	
	function showLoadinfo(){
		
		$('div.formError').remove();
		
		var date = new Date();
		loadinfoTimer = date.getTime();
		$('#submit-btn').attr('disabled', 'disabled').addClass('disabled');
		$('div.gcheckout-onepage-wrap').append('<div class="loadinfo">'+loadinfo_text+'</div>');
	}
	function hideLoadinfo(){
		
		if(loadinfoTimeOut){
			clearTimeout(loadinfoTimeOut);
		}
		
		var date = new Date();
		
		if(!loadinfoTimer || loadinfoTimer < (date.getTime()-1200)){
			$('div.gcheckout-onepage-wrap .loadinfo').fadeOut(300, function(){$(this).remove();});
			$('#submit-btn').attr('disabled', '').removeClass('disabled');
		}else{
			
			loadinfoTimeOut = setTimeout(function(){
				$('div.gcheckout-onepage-wrap .loadinfo').fadeOut(300, function(){$(this).remove();});
				$('#submit-btn').attr('disabled', '').removeClass('disabled');
			}, (loadinfoTimer+1200-date.getTime()));
		}
	}
	
	function updateInformation(){
		if(loadinfoTimeOut){
			clearTimeout(loadinfoTimeOut);
		}
		
		loadinfoTimeOut = null;
		
		//loadinfoTimeOut = setTimeout(function(){
			showLoadinfo();
			
			$('#gcheckout-onepage-form').attr('action', getMethodsUrl).submit();
			
		//}, 1200);
	}
	function updatePayment(){
		if(loadinfoTimeOut){
			clearTimeout(loadinfoTimeOut);
		}
		
		loadinfoTimeOut = null;
		//loadinfoTimeOut = setTimeout(function(){
			showLoadinfo();
			
			$('#gcheckout-onepage-form').attr('action', getPaymentMethodsUrl).submit();
			
		//}, 1200);
	}
	function updateShipping(){
		if(loadinfoTimeOut){
			clearTimeout(loadinfoTimeOut);
		}
		

		showLoadinfo();
		
		$('#gcheckout-onepage-form').attr('action', getShippingMethodsUrl).submit();
		
	}
	
	
	$('#gcheckout-onepage-address .billing-country select, #gcheckout-onepage-address .billing_postcode input, #gcheckout-onepage-address .billing_city input, #gcheckout-onepage-address .billing-region input, #gcheckout-onepage-address .billing-region select').bind('change', function(){
		
		if($('#billing_use_for_shipping_yes').attr('checked') || $('#billing_use_for_shipping_yes').length == 0){
			updateInformation();
		}else{
			updatePayment();
		}
		
	});
	
	$('#gcheckout-onepage-address .shipping-country select, #gcheckout-onepage-address .shipping_postcode input, #gcheckout-onepage-address .shipping_city input, #gcheckout-onepage-address .shipping-region input, #gcheckout-onepage-address .shipping-region select').bind('change', function(){
		
		if(!$('#billing_use_for_shipping_yes').attr('checked')){
			updateShipping();
		}
		
	});
	
	$('#billing_use_for_shipping_yes').live('click', function(){
    	if(this.checked){
    		$('#gcheckout-shipping-address input[class*=validate]').each(function(){
    			$('div.'+this.id+'formError').remove();
    		});
    		
    		$('#shipping-num, #deliverydate-num, #payment-num, #review-num').each(function(){
			
				$(this).text(parseInt($(this).text())-1);
				
			});
    		
    	}else{
    		$('#shipping-num, #deliverydate-num, #payment-num, #review-num').each(function(){
			
				$(this).text(parseInt($(this).text())+1);
				
			});
    	}
    	updateShipping();
    });

	
	
	$('#gcheckout-onepage-address .input-box-country select, #gcheckout-onepage-address .input-box-postcode input, #gcheckout-onepage-address .input-box-city input, #gcheckout-onepage-address .input-box-region input, #gcheckout-onepage-address .input-box-region select').focus(function(){
		if(loadinfoTimeOut){
			clearTimeout(loadinfoTimeOut);
		}
	});
	
	$('#gcheckout-shipping-method-available input[type=radio]').live('click', function(){
		if(this.checked){
			
			showLoadinfo();
			checkedShipping = this.id;
			$('#gcheckout-onepage-form').attr('action', getTotalsUrl).submit();
		}
	});
	
	
	/*if(default_shipping_method && (input = $('#gcheckout-shipping-method-available input[value='+default_shipping_method+']').get(0))){
		$(input).attr('checked', 'checked').trigger('click').change();
	}*/
	
	if(default_payment_method && !$('#gcheckout-payment-methods input[checked=checked]').get(0) && (input = $('#gcheckout-payment-methods input[value='+default_payment_method+']').get(0))){
		$(input).attr('checked', 'checked').trigger('click').trigger('change');
	}
	
	
});


var paymentForm = Class.create();
paymentForm.prototype = {
	beforeInitFunc:$H({}),
    afterInitFunc:$H({}),
    beforeValidateFunc:$H({}),
    afterValidateFunc:$H({}),
    initialize: function(formId){
        this.form = $(this.formId = formId);
    },
    init : function () {
        var elements = Form.getElements(this.form);
        /*if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }*/
        var method = null;
        for (var i=0; i<elements.length; i++) {
            if (elements[i].name=='payment[method]') {
                if (elements[i].checked) {
                    method = elements[i].value;
                }
            }
            elements[i].setAttribute('autocomplete','off');
        }
        if (method) this.switchMethod(method);
    },
    
    switchMethod: function(method){
        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
        	
        	jQuery('#payment_form_'+this.currentMethod).find('input,select,textarea').each(function(){
        		
        		jQuery('div.'+this.id+'formError').remove();
        		
        	});
        	
            var form = $('payment_form_'+this.currentMethod);
            form.style.display = 'none';
            var elements = form.getElementsByTagName('input');
            for (var i=0; i<elements.length; i++) elements[i].disabled = true;
            var elements = form.getElementsByTagName('select');
            for (var i=0; i<elements.length; i++) elements[i].disabled = true;
            

        }
        if ($('payment_form_'+method)){
            var form = $('payment_form_'+method);
            form.style.display = '';
            var elements = form.getElementsByTagName('input');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;
            var elements = form.getElementsByTagName('select');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;
            this.currentMethod = method;
        }
    }
}
