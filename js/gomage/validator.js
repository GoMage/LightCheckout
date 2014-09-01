/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.7
 * @since        Class available since Release 1.0 
 */ 

var glc_exists_customer_msg = 'There is already a customer registered using this email address. Please login using this email address or enter a different email address.';
if (typeof Translator != 'undefined'){
	glc_exists_customer_msg = Translator.translate(glc_exists_customer_msg);
}

Validation.add('glc-exists-customer', glc_exists_customer_msg,		
function(v) {	
	if (typeof checkout != 'undefined'){
		return !checkout.exists_customer;
	}	
	return true;
});

function glcValidateTaxvatBilling(v) {
    if ($('billing_country_id') && (checkout.taxvat_verify_result != null)){
        if(vat_required_countries.indexOf($('billing_country_id').value) !== -1){
            if($('buy_without_vat') && $('buy_without_vat').checked && !checkout.taxvat_verify_result.billing){
                return false;
            }else if(v && !checkout.taxvat_verify_result.billing){
                return false;
            }
        }
    }
    return true;
}

function glcValidateTaxvatShipping(v) {
    if ($('shipping_country_id') && $('shipping_country_id').visible() && (checkout.taxvat_verify_result != null)){
        if(vat_required_countries.indexOf($('shipping_country_id').value) !== -1){
            if($('buy_without_vat') && $('buy_without_vat').checked && !checkout.taxvat_verify_result.shipping){
                return false;
            }else if(v && !checkout.taxvat_verify_result.shipping){
                return false;
            }
        }
    }
    return true;
}

Object.extend(Validation, {
	defaultOptions:{
    onSubmit : true,
    stopOnFirst : false,
    immediate : false,
    focusOnError : false,
    useTitles : false,
    addClassNameToContainer: false,
    containerClassName: '.input-box',
    onFormValidate :  function(result, form) {Validation.AfterFormValidate(result, form)},
    onElementValidate : function(result, elm) {}
	},
	
	AfterFormValidate: function(result, form){		
        if(!result){
			$$('div.validation-advice,div.validation-advice-rtl').each(function(e){
				if(e.style.display != 'none'){
					e.scrollTo();
					throw $break;
				}
			});			
        }else{        
        	$('submit-btn').addClassName('disabled').disabled = true;        
        }
    },
	
	createAdvice : function(name, elm, useTitle, customError) {
		
        var v = Validation.get(name);
        var errorMsg = useTitle ? ((elm && elm.title) ? elm.title : v.error) : v.error;
        if (customError) {
            errorMsg = customError;
        }
        try {
            if (Translator){
                errorMsg = Translator.translate(errorMsg);
            }
        }
        catch(e){}

        var useRtl = false;
        if (typeof $(elm).up('div.glc-rtl') != 'undefined'){
			useRtl = true;
        }

        advice = '<div class="validation-advice'+(useRtl?'-rtl':'')+'" id="advice-' + name + '-' + Validation.getElmID(elm) +'" style="display:none;"><div class="validation-advice-container">' + errorMsg + '</div></div>'

        Validation.insertAdvice(elm, advice);
        advice = Validation.getAdvice(name, elm);
        //if($(elm).hasClassName('absolute-advice')) {
            var dimensions = $(elm).getDimensions();
            var originalPosition = Position.cumulativeOffset(elm);
			
            advice._adviceTop = (originalPosition[1]);
            if (useRtl){
            	advice._adviceLeft = (originalPosition[0]-100+(elm.offsetWidth > 40 ? 0: -21));
            }else{
            	advice._adviceLeft = (originalPosition[0]+(elm.offsetWidth > 40 ? elm.offsetWidth-40: -21));
            }
            advice._adviceWidth = (dimensions.width);
            advice._adviceAbsolutize = true;
        //}
        return advice;
    },
    test : function(name, elm, useTitle) {
        var v = Validation.get(name);
        var prop = '__advice'+name.camelize();
        try {
        if(Validation.isVisible(elm) && !v.test($F(elm), elm)) {
            //if(!elm[prop]) {
                var advice = Validation.getAdvice(name, elm);
                if (advice == null) {
                    advice = this.createAdvice(name, elm, useTitle);
                }else{
                	
	                	var useRtl = false;
	                    if (typeof $(elm).up('div.glc-rtl') != 'undefined'){
	            			useRtl = true;
	                    }
                	
                	//if($(elm).hasClassName('absolute-advice')) {
			            var dimensions = $(elm).getDimensions();
			            var originalPosition = Position.cumulativeOffset(elm);
						
			            advice._adviceTop = (originalPosition[1]);
			            if (useRtl){
			            	advice._adviceLeft = (originalPosition[0] - 100 + (elm.offsetWidth > 40 ? 0: -21));
			            }else{
			            	advice._adviceLeft = (originalPosition[0] + (elm.offsetWidth > 40 ? elm.offsetWidth-40: -21));
			            }
			        //}
			        
                }
                this.showAdvice(elm, advice, name);
                this.updateCallback(elm, 'failed');
            //}
            elm[prop] = 1;
            if (!elm.advaiceContainer) {
                elm.removeClassName('validation-passed');
                elm.addClassName('validation-failed');
            }

           if (Validation.defaultOptions.addClassNameToContainer && Validation.defaultOptions.containerClassName != '') {
                var container = elm.up(Validation.defaultOptions.containerClassName);
                if (container && this.allowContainerClassName(elm)) {
                    container.removeClassName('validation-passed');
                    container.addClassName('validation-error');
                }
            }
            return false;
        } else {
        	
            var advice = Validation.getAdvice(name, elm);
            this.hideAdvice(elm, advice);
            this.updateCallback(elm, 'passed');
            elm[prop] = '';
            elm.removeClassName('validation-failed');
            elm.addClassName('validation-passed');
            if (Validation.defaultOptions.addClassNameToContainer && Validation.defaultOptions.containerClassName != '') {
                var container = elm.up(Validation.defaultOptions.containerClassName);
                if (container && !container.down('.validation-failed') && this.allowContainerClassName(elm)) {
                    if (!Validation.get('IsEmpty').test(elm.value) || !this.isVisible(elm)) {
                        container.addClassName('validation-passed');
                    } else {
                        container.removeClassName('validation-passed');
                    }
                    container.removeClassName('validation-error');
                }
            }
            return true;
        }
        } catch(e) {
            throw(e)
        }
    },
    isVisible : function(elm) {
        while(elm.tagName != 'BODY') {
            if(!$(elm).visible()) return false;
            elm = elm.parentNode;
        }
        return true;
    },
	insertAdvice : function(elm, advice){
        
        var temp = document.createElement('DIV');
        
        temp.innerHTML = advice;
        
        document.body.appendChild(temp.firstChild);
        
        
        
    },
    showAdvice : function(elm, advice, adviceName){
    	
        if(!elm.advices){
            elm.advices = new Hash();
        }
        else{
            elm.advices.each(function(pair){                
                pair.value.hide();
            }.bind(this));
        }
        
        elm.advices.set(adviceName, advice);
        if(typeof Effect == 'undefined') {
            advice.style.display = 'block';
        } else {
        	
            if(!advice._adviceAbsolutize) {
                new Effect.Appear(advice, {duration : 1 });
            } else {
            	
                Position.absolutize(advice);
                advice.show();
                
                advice.setStyle({
                    'top':advice._adviceTop + 'px',
                    'left': advice._adviceLeft + 'px',
                    'width': 'auto',
                    'height':'auto',
                    'z-index': 1000,
                    'opacity': 1
                });
                
                
                advice.addClassName('advice-absolute');
                
                advice.setStyle({
                    'top':advice._adviceTop -advice.offsetHeight - 8 + 'px'
                });
                
                
                
                if(advice.select('.arrow').length == 0){
                	var arrowhtml = '<div class="arrow" style="top:'+(advice.offsetHeight-2)+'px"><div class="line10"></div><div class="line9"></div><div class="line8"></div><div class="line7"></div><div class="line6"></div><div class="line5"></div><div class="line4"></div><div class="line3"></div><div class="line2"></div><div class="line1"></div></div>';
                
                	advice.insert(arrowhtml);
                
                	Event.observe(advice, 'click', function(){
            			Validation.hideAdvice(this.elm,this.advice);
            		}.bind({elm:elm,advice:advice}));
                }else{
                	
                	advice.select('.arrow')[0].top = (advice.offsetHeight-2)+'px';
                }
                
            }
        }
    }
});


