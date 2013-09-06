/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 3.1 
 */ 

var processing_done = false;
Event.observe(window, 'load', function(){
	if (typeof isCcApiEnabled != 'undefined' && isCcApiEnabled) {
        if (typeof payment != 'undefined' && typeof payment.save != 'undefined') {
            payonePaymentWrap();
        }
        if (typeof order != 'undefined' && typeof order.submit != 'undefined') {
            payoneOrderWrap();
            prepareParamsWrap();
        }
        
        checkout.LightcheckoutSubmit = checkout.LightcheckoutSubmit.wrap(function(original){
            if ($('p_method_payone_cc') && $('p_method_payone_cc').checked && processing_done == false) {                    
                var elements = $('fieldset_payone_cc').select('input[type="hidden"]');
                enableElements(elements);
                $('payment_form_payone_cc').contentWindow.updateHiddenElements(elements);
                application.isAdmin = true;
                application.getApplicationParams();                    
            } else {
                original();
            }
        });
        openPaymentSectionWrap();                
    }
});

function enableElements(elements) {
	for (var i=0; i<elements.length; i++) {
    	elements[i].disabled = false;
	}
}
function payoneDisplayCcForm() {
	$('payment_form_payone_cc').contentWindow.show('payone_cc_iframe_cont');
	$('payment_form_payone_cc').contentWindow.hide('payoneDisplayCcFormLink');
    $('hidden_cc_additional_data').setAttribute('value', '');
}

function payonePaymentWrap() {
	payment.save = payment.save.wrap(function(origSaveMethod){
		if ($('p_method_payone_cc') && $('p_method_payone_cc').checked) {
			if (checkout.loadWaiting!=false) return;
			
			var elements = $('fieldset_payone_cc').select('input[type="hidden"]');
			enableElements(elements);
			if ($('hidden_cc_additional_data').value.length==0) {
				if (this.validate() && $('payment_form_payone_cc').contentWindow.validate()) {
					checkout.setLoadWaiting('payment');
					$('payment_form_payone_cc').contentWindow.updateHiddenElements(elements);
	                application.getApplicationParams();
				}
			}
			else {
				origSaveMethod();
			}
		} else {
			origSaveMethod();
		}
	});
}
function payoneOrderWrap() {
    order.submit = order.submit.wrap(function(origSaveMethod){
        if ($('p_method_payone_cc') && $('p_method_payone_cc').checked) {
            if(editForm.validator.validate() && $('payment_form_payone_cc').contentWindow.validate()){
            	var elements = $('fieldset_payone_cc').select('input[type="hidden"]');
				$('payment_form_payone_cc').contentWindow.updateHiddenElements(elements);
                application.isAdmin = true;
                application.getApplicationParams();
            }
        } else {
            origSaveMethod();
        }
        
    });        
}

function prepareParamsWrap() {
    order.prepareParams = order.prepareParams.wrap(function(origMethod, origParams){
        if ($('p_method_payone_cc') && $('p_method_payone_cc').checked) {
            var params = origMethod(origParams);
            params['cc_owner'] = '';
            params['cc_type'] = '';
            params['cc_number'] = '';
            params['cc_exp_month'] = '';
            params['cc_exp_year'] = '';
            params['cc_cid'] = '';
            return params;
        } else {
            return origMethod(origParams);
        }
    });
}

/**
 * Reloads the payone CC form.
 * This allows the payone wrappers to function even when the user switches the checkout steps via the accordion or the progress bar
 */
function openPaymentSectionWrap() {
    if (typeof accordion != 'undefined'){
        accordion.openSection = accordion.openSection.wrap(
            function(origMethod, origParams)
            {
                var section = $(origParams);
                if(section.id == 'opc-payment' && accordion.currentSection != 'opc-shipping_method' && payment.currentMethod == 'payone_cc')
                {
                    // Reload the CC form:
                    payoneDisplayCcForm();
                }

                // Call original method
                return origMethod(origParams);
            }
        );
    }
}



var saveSessInfoUrlGlobal = false;
var payoneApplication = Class.create();
    payoneApplication.prototype = {
        initialize: function(initUrl, saveSessInfoUrl){
            this.isError = false;
            this.error = '';
            this.isAdmin = false;
            this.initUrl = initUrl;
            saveSessInfoUrlGlobal = saveSessInfoUrl;
            this.data = {
                request : 'creditcardcheck',
                storecarddata : 'yes'
            }
            this.onSuccessGetParams = this.initApplication.bindAsEventListener(this);
            this.onCompleteGetParams = this.payoneCcSave.bindAsEventListener(this);
        },
        getApplicationParams: function()
        {
            if (typeof checkout != 'undefined') {
                var request = new Ajax.Request(
                    this.initUrl,
                    {
                        method:'post',
                        onComplete: this.onCompleteGetParams,
                        onSuccess: this.onSuccessGetParams,
                        onFailure: checkout.ajaxFailure.bind(checkout),
                        parameters: ''
                    }
                );
            } else {
                var request = new Ajax.Request(
                    this.initUrl,
                    {
                        method:'post',
                        onComplete: this.onCompleteGetParams,
                        onSuccess: this.onSuccessGetParams,
                        parameters: ''
                    }
                );
            }
        },
        initApplication: function(transport)
        {
            if (transport && transport.responseText){
                try{
                    response = eval('(' + transport.responseText + ')');
                }
                catch (e) {
                    response = {};
                }
            }
            this.data.mode = response.mode;
			this.data.mid = response.mid;
            this.data.aid = response.aid;
            this.data.portalid = response.portalid;
            this.data.language = response.language;
            this.data.encoding = response.encoding;
            this.data.hash = response.hash;
			this.data.cardholder = $('payment_form_payone_cc').contentWindow.getInputValue('payone_cc_cc_owner');
			this.data.cardpan = $('payment_form_payone_cc').contentWindow.getInputValue('payone_cc_cc_number');
			this.data.cardtype = getCardType($('payment_form_payone_cc').contentWindow.getInputValue('payone_cc_cc_type'));
			this.data.cardexpiremonth = $('payment_form_payone_cc').contentWindow.getInputValue('payone_cc_expiration');
			this.data.cardexpireyear = $('payment_form_payone_cc').contentWindow.getInputValue('payone_cc_expiration_yr');
			this.data.cardcvc2 = $('payment_form_payone_cc').contentWindow.getInputValue('payone_cc_cc_cid');
        },
        payoneCcSave: function()
        {
            var cllBack = this.isAdmin ? 'processPayoneResponseAdmin' : 'processPayoneResponse';
            var request = new PayoneRequest(this.data, {
                return_type : 'object',
                callback_function_name : cllBack
            });
            request.checkAndStore();            
        }
    }
function processPayoneResponse(response)
{
    if (response.get('status') != 'VALID') {
    	alert(response.get('customermessage'));
        checkout.setLoadWaiting(false);
    } else {
        $('hidden_cc_additional_data').value = response.get('pseudocardpan');
        $('hidden_cc_truncatedcardpan').value = response.get('truncatedcardpan');
        
        if (saveSessInfoUrlGlobal) {
	        new Ajax.Request(
	        	saveSessInfoUrlGlobal,
	            {
	                method: 'post',
	                parameters: Form.serialize(payment.form)
	            }
	        );
        }
        processing_done = true;
        checkout.LightcheckoutSubmit();
    }
}

function processPayoneResponseAdmin(response)
{
    if (response.get('status') != 'VALID') {
    	alert(response.get('customermessage'));
    } else {
    	var elements = $('fieldset_payone_cc').select('input[type="hidden"]');
		enableElements(elements);
        $('hidden_cc_additional_data').value = response.get('pseudocardpan');
        processing_done = true;
        checkout.LightcheckoutSubmit();
    }
}
function getCardType(cardtype){
	if (cardtype == "VI") return "V";
	else if(cardtype == "MC") return "M";
	else if(cardtype == "AE") return "A";
    else if(cardtype == "MCI") return "O";
    else if(cardtype == "JCB") return "J";
    else if(cardtype == "DI") return "C";
	else return "";
}