Lightcheckout = Class.create({
	billing_taxvat_enabled:false,
	billing_taxvat_verified_flag:false,
	url:'',
	existsreview: false,
	initialize:function(data){
		
		if(data && (typeof data.billing_taxvat_enabled != 'undefined')){
			this.billing_taxvat_enabled = data.billing_taxvat_enabled;
		}
		
		(data && data.url) ? this.url = data.url : '';
		
		if(typeof observe_billing_items != 'undefined'){
			$$(observe_billing_items).each(function(e){
				
				if(e.type == 'radio' || e.type == 'checkbox'){
					
					e.observe('click', function(){
						this.submit(this.getFormData(), 'get_methods');
					}.bind(this));
					
				}else{
					
					e.observe('change', function(){
						this.submit(this.getFormData(), 'get_methods');
					}.bind(this));
					
				}
			}.bind(this));
			
		}
		
		if(typeof observe_shipping_items != 'undefined'){
			$$(observe_shipping_items).each(function(e){
				
				if(e.type == 'radio' || e.type == 'checkbox'){
					
					e.observe('click', function(){
						this.submit(this.getFormData(), 'get_shipping_methods');
					}.bind(this));
					
				}else{
					
					e.observe('change', function(){
						this.submit(this.getFormData(), 'get_shipping_methods');
					}.bind(this));
					
				}
				
				
			}.bind(this));
			
		}
		
		if($('billing_use_for_shipping_yes')){
		$('billing_use_for_shipping_yes').observe('click', function(e){
    		this.submit(this.getFormData(), 'get_shipping_methods');
    	}.bind(this));
    	}
    	
    	$('gcheckout-onepage-address').select('select, input, textarea').each(function(e){
    		
    		if(e.hasClassName('required-entry') && !e.hasClassName('validate-taxvat')){
    			e.observe('blur', function(){
    			Validation.validate(this,{useTitle : checkoutForm.validator.options.useTitles, onElementValidate : checkoutForm.validator.options.onElementValidate});
    			});
    		}
    		
    	});
    	
    	if ($('use_reward_points')) {
    		$('use_reward_points').observe('click', function(e){
        		this.submit(this.getFormData(), 'get_totals');
        	}.bind(this));
    	}	
		
		this.observeMethods();
		this.observeAddresses();
	},
	
	
	observeMethods:function(){
		$$('#gcheckout-shipping-method-available input').each(function(e){
			e.onchange = null;
			Event.stopObserving(e, 'click');
			e.observe('click', function(e){
				if(this.elem.checked){
					this.obj.submit(this.obj.getFormData(), 'get_totals');
				}
			}.bind({elem:e,obj:this}));
		}.bind(this));
		$$('#gcheckout-onepage-form input[name=shipping_method]').each(function(e){
			e.addClassName('validate-one-required-by-name');
			throw $break;
		});
		
		
		var payment_elms = $$('#checkout-payment-method-load input[name="payment[method]"]');
		
		payment_elms.each(function(e){
			e.addClassName('validate-one-required-by-name');
			throw $break;
		});
		
		
		payment_elms.each(function(e){
			
			if(e.checked){
				eval(e.getAttribute('onclick'));
				throw $break;
			}
		});
		
		
		if(default_payment_method && (input = $$('#gcheckout-payment-methods input[value="'+default_payment_method+'"]')[0])){
			
			input.checked = true;
			
			eval(input.getAttribute('onclick'));
			
		}
		
		if(toggleToolTip){
		
		if($('payment-tool-tip-close')){
            Event.observe($('payment-tool-tip-close'), 'click', toggleToolTip);
        }
        $$('.cvv-what-is-this').each(function(element){
            Event.observe(element, 'click', toggleToolTip);
        });
        
        }
		
		this.observePaymentMethods();
		
	},
	
	observePaymentMethods:function(){
		$$('#checkout-payment-method-load input[name="payment[method]"]').each(function(e){
			e.observe('click', function(e){
				if(this.elem.checked){
					if (default_payment_method)
						default_payment_method = this.elem.value;
					this.obj.submit(this.obj.getFormData(), 'get_totals');
				}
			}.bind({elem:e,obj:this}));
		}.bind(this));
		$$('.cvv-what-is-this').each(function(element){
            Event.observe(element, 'click', toggleToolTip);
        });
	},	
	
	getFormData:function(){
		
		var elements = $('gcheckout-onepage-form').getElements('input, select, textarea');
		
		var query = '';
		
		for(var i = 0;i < elements.length;i++){
			if(((elements[i].type == 'checkbox') || elements[i].type == 'radio') && !elements[i].checked){
				continue;
			}	
			
			if (elements[i].disabled)
			{
				continue;
			}	
			
			if(query != ''){
				query = query + '&';
			}
			
			query = query+elements[i].name + '=' + elements[i].value;						
			
		}				
		return query;
		
	},
	applyDisocunt:function(flag){
		
		if (flag){
	        $('remove_coupone').value = 1;
			this.submit({coupon_code:$('coupon_code').value,remove:$('remove_coupone').value}, 'discount');
		}else{
	        $('remove_coupone').value = 0;
			this.submit({coupon_code:$('coupon_code').value,remove:$('remove_coupone').value}, 'discount');
		}
		
	},
	
	LightcheckoutSubmit: function(){
		
		if((payment.currentMethod.indexOf('sagepay') == 0) && 
		   (SageServer != undefined) && (review != undefined))
		{			
			if (checkoutForm.validator.validate())
			{	
				review.preparedata(); 
			}	
		}
		else
		{
			if (checkoutForm.validator.validate()){
				this.submit(this.getFormData(), 'save_payment_methods');				
			}
		}	
	},
		
	innerHTMLwithScripts: function(element, content){
		var js_scripts = content.extractScripts();										
		element.innerHTML = content.stripScripts();
		for (var i=0; i< js_scripts.length; i++){																
	        if (typeof(js_scripts[i]) != 'undefined'){        	        	
	        	LightcheckoutglobalEval(js_scripts[i]);                	
	        }
	    }			
	},
	
	submit:function(params, action){
		
		this.showLoadinfo();
		
		if(typeof params != "string"){
			parameters = new Array();
			for(key in params){
				
				parameters.push(key+'='+params[key]);
				
			}
			
			params = parameters.join('&');
			
		}
		
		var request = new Ajax.Request(this.url,
		  {
		    method:'post',
		    parameters:'action='+action+'&'+params,
		    onSuccess: function(transport){
		      
		    eval('var response = '+transport.responseText);
		    
		    
		    
		    if(response.url){
		    	
		    	this.existsreview = false;
		    	setLocation(response.url);
		    	
		    }else{
		    
		    if(response.error){
				
				if(response.message){
				alert(response.message);
				}
				this.existsreview = false;
				this.hideLoadinfo();
				
			}else{
												
				if(response.section == 'methods'){
					
					if(response.rates){
						if(shipping_rates_block = $('gcheckout-shipping-method-available')){
							this.innerHTMLwithScripts(shipping_rates_block, response.rates);
						}
					}
					if(response.payments){
						this.innerHTMLwithScripts($('gcheckout-payment-methods-available'), response.payments);						
						payment.init();
						this.observePaymentMethods();
					}
					
					this.innerHTMLwithScripts($$('#gcheckout-onepage-review div.totals')[0], response.totals);									
					
					if (response.gift_message){
						if(giftmessage_block = $('gomage-lightcheckout-giftmessage')){
							
							this.innerHTMLwithScripts(giftmessage_block, response.gift_message);
							
						}
					}
					
					if(response.toplinks){
						var link = $$('ul.links a.top-link-cart')[0];		
						if (link && response.toplinks){				
							
							var content = response.toplinks;			
							if (content && content.toElement){
						    	content = content.toElement();			    	
						    }else if (!Object.isElement(content)){			    	
							    content = Object.toHTML(content);
							    var tempElement = document.createElement('div');			    
							    tempElement.innerHTML = content;
							    el =  this.getElementsByClassName('top-link-cart', tempElement);
							    if (el.length > 0){
							        content = el[0];
							    }
							    else{
							       return;
							    }
						    }								
							link.parentNode.replaceChild(content, link);							
						}
					}

					
					this.observeMethods();
					
				}else if(response.section == 'payment_methods'){
					
					if(response.payments){						
						this.innerHTMLwithScripts($('gcheckout-payment-methods-available'), response.payments);
						payment.init();
						this.observePaymentMethods();
					}
										
					this.innerHTMLwithScripts($$('#gcheckout-onepage-review div.totals')[0], response.totals);
					
					
				}else if(response.section == 'shiping_rates'){
					
					if(response.rates){
						if(shipping_rates_block = $('gcheckout-shipping-method-available')){						
							this.innerHTMLwithScripts(shipping_rates_block, response.rates);
						}
					}
					
					this.innerHTMLwithScripts($$('#gcheckout-onepage-review div.totals')[0], response.totals);
					
					this.observeMethods();
					
				}else if(response.section == 'totals'){
					
					this.innerHTMLwithScripts($$('#gcheckout-onepage-review div.totals')[0], response.totals);
					
					if(response.rates){
						if(shipping_rates_block = $('gcheckout-shipping-method-available')){
							this.innerHTMLwithScripts(shipping_rates_block, response.rates);
						}
						this.observeMethods();
					}	
					
					if(response.payments){
						this.innerHTMLwithScripts($('gcheckout-payment-methods-available'), response.payments);
						payment.init();
						this.observePaymentMethods();
					}
					
				}else if(response.section == 'varify_taxvat'){
					
					if(response.rates){
						if(shipping_rates_block = $('gcheckout-shipping-method-available')){
							this.innerHTMLwithScripts(shipping_rates_block, response.rates);
						}
					}
					if(response.payments){
						this.innerHTMLwithScripts($('gcheckout-payment-methods-available'), response.payments);
						payment.init();						
					}
															
					this.innerHTMLwithScripts($$('#gcheckout-onepage-review div.totals')[0], response.totals);
					
					if($('billing_taxvat_verified')){
						$('billing_taxvat_verified').remove();
					}
					
					checkout.billing_taxvat_verified_flag = response.verify_result;
					
					if(response.verify_result){
						
						if(label = $('billing_taxvat').parentNode.parentNode.getElementsByTagName('label')[0]){
						
						label.innerHTML += '<strong id="billing_taxvat_verified" style="margin-left:5px;">(<span style="color:green;">Verified</span>)</strong>';
						
						}
						
					}else if($('billing_taxvat') && $('billing_taxvat').value){
						
						if(label = $('billing_taxvat').parentNode.parentNode.getElementsByTagName('label')[0]){
						
						label.innerHTML += '<strong id="billing_taxvat_verified" style="margin-left:5px;">(<span style="color:red;">Not Verified</span>)</strong>';
						
						}
					}
					
					this.observeMethods();
					
				} else if (response.section == 'centinel'){
					
					if (response.centinel){
						if(centinel_block = $('gcheckout-payment-centinel')){
							this.innerHTMLwithScripts(centinel_block, response.centinel);							
						}
					}else{
						if((payment.currentMethod == 'authorizenet_directpost') && ((typeof directPostModel != 'undefined'))){
							directPostModel.saveOnepageOrder();
						}else{
							checkoutForm.submit();
						}								
					}					
				}
				
				if (this.existsreview)
				{
					this.existsreview = false;
					review.save();
				}	
				else
				{	
					this.hideLoadinfo();
				}	
				
			}
			
			}
		      
		    }.bind(this),
		    onFailure: function(){
		    	this.existsreview = false;
		    }
		  });
	},
	
	getElementsByClassName:function(classname, node){
	    var a = [];
	    var re = new RegExp('\\b' + classname + '\\b');
	    var els = node.getElementsByTagName("*");
	    for(var i=0,j=els.length; i<j; i++){
	           if(re.test(els[i].className))a.push(els[i]);
	    } 
	    return a;
	},
	
	showLoadinfo:function(){
		
		$('submit-btn').disabled = 'disabled';
		$('submit-btn').addClassName('disabled');
		
		$$('.validation-advice').each(function(e){e.remove()});
		
		msgs = $$('ul.messages');
		
		if(msgs.length){
		
			for(i = 0; i < msgs.length;i++){
			
			msgs[i].fade();
			
			}
		
		}
		
		$$('div.gcheckout-onepage-wrap')[0].insert('<div class="loadinfo">'+loadinfo_text+'</div>');
	},
	hideLoadinfo:function(){
		
		var e = $$('div.gcheckout-onepage-wrap .loadinfo')[0];
		
		e.parentNode.removeChild(e);
		
		$('submit-btn').disabled = false;
		$('submit-btn').removeClassName('disabled');
		
	},
	ajaxFailure: function(){
        location.href = this.failureUrl;
    }, 
	showOverlay:function(){
		var overlay = $('gomage-checkout-main-overlay');
		
		if(overlay){
			
			overlay.show();
			
		}else{
			
			overlay = document.createElement('div');
			overlay.id = 'gomage-checkout-main-overlay';
			overlay.className = 'gomage-checkout-overlay';
			
			document.body.appendChild(overlay);
		}
		return overlay;
	},
	hideOverlay:function(){
		var overlay = $('gomage-checkout-main-overlay');
		
		if(overlay){
			overlay.hide();
		}
	},
	showLoginForm:function(){
		var overlay = this.showOverlay();
		
		overlay.onclick = function(){
			this.hideLoginForm();
		}.bind(this);
		
		
		
		var loginForm = $('login-form');
		
		if(!loginForm){
			
			$$('body')[0].insert(loginFormHtml);
			
			var loginForm = $('login-form');
		}
		
		loginForm.style.position = 'fixed';
		loginForm.style.display = 'block';
		
		
		var left	= loginForm.offsetWidth/2;
		
		var contentHeight = document.documentElement ? document.documentElement.clientHeight : document.body.clientHeigh;
		
		var top		= contentHeight/2.4 - loginForm.offsetHeight/2;
		
		loginForm.style.left = '50%';
		loginForm.style.marginLeft = '-'+left+'px';
		loginForm.style.top	= top	+'px';
	},
	hideLoginForm:function(){
		this.hideOverlay();
		
		var loginForm = $('login-form');
		
		if(loginForm){
			
			loginForm.hide();
		}
		
	},
	showTerms:function(){
		var overlay = this.showOverlay();
		
		overlay.onclick = function(){
			this.hideTerms();
		}.bind(this);
		
		
		var termsBlock = $('terms-block');
		
		
		if(!termsBlock){
			
			$$('body')[0].insert(termsHtml);
			
			var termsBlock = $('terms-block');
		}
		
		termsBlock.style.position = 'fixed';
		termsBlock.style.display = 'block';
		
		//var scrolloffset = document.body.cumulativeScrollOffset();
		
		var left	= termsBlock.offsetWidth/2;
		
		var contentHeight = document.documentElement ? document.documentElement.clientHeight : document.body.clientHeigh;
		
		var top		= contentHeight/2 - termsBlock.offsetHeight/2;
		
		
		termsBlock.style.left = '50%';
		termsBlock.style.marginLeft = '-'+left+'px';
		termsBlock.style.top	= top	+'px';
	},
	hideTerms:function(){
		this.hideOverlay();
		
		var loginForm = $('terms-block');
		
		if(loginForm){
			
			loginForm.hide();
		}
		
	},
    loadAddress:function(type, id, url){
    	
    	if(id){

		
			this.showLoadinfo();
			
			var request = new Ajax.Request(url,
			  {
			    method:'post',
			    parameters:{'id':id, 
				            'type':type, 
				            'use_for_shipping': $('billing_use_for_shipping_yes').checked
				           },
			    onSuccess: function(transport){
			      
			    eval('var response = '+transport.responseText);
			      
			    if(response.error){
					
					
					
				}else{
					
					if(response.rates){
						if ($('gcheckout-shipping-method-available')){
							this.innerHTMLwithScripts($('gcheckout-shipping-method-available'), response.rates);
						}	
					}
					if(response.payments){
						this.innerHTMLwithScripts($('gcheckout-payment-methods-available'), response.payments);						
						payment.init();						
					}
					
					if (response.content_billing){
						var div_billing = document.createElement('div');					
						div_billing.innerHTML = response.content_billing;
						$('gcheckout-onepage-address').replaceChild(div_billing.firstChild, $('gcheckout-billing-address'));
					}
					
					if (response.content_shipping){
						var div_shipping = document.createElement('div');					
						div_shipping.innerHTML = response.content_shipping;
						$('gcheckout-onepage-address').replaceChild(div_shipping.firstChild, $('gcheckout-shipping-address'));
					}	
					
					if(response.totals){
						this.innerHTMLwithScripts($$('#gcheckout-onepage-review div.totals')[0], response.totals);
					}
					
					initAddresses();
					
					checkout.initialize();
				}
				this.hideLoadinfo();
			      
			    }.bind(this),
			    onFailure: function(){
			    	//...
			    }
			  });
    		    	
    	}else{
	    	
	    	$(type+'-new-address-form').select('input[type=text], select, textarea').each(function(e){
	    		
	    		e.value = '';
	    		
	    	});
    	
    	}

    },
    observeAddresses:function(){
    	
    	if(this.billing_taxvat_enabled){
    	
	    	if($('billing_taxvat')){
	    	
	    	$('billing_taxvat').observe('change', function(){
	    		if(vat_required_countries.indexOf($('billing_country_id').value) !== -1){
	    		checkout.submit(checkout.getFormData(), 'varify_taxvat')
	    		}
	    	});
	    	
	    	}
    	
    	}
    	
	    if(checkoutloginform.customerIsCustomerLoggedIn){
	    $('gcheckout-billing-address').select('select, input').each(function(element){
	    	
	    	if(element.name == 'billing_address_id'){
	    		
	    		element.observe('change', function(e){
	    			if(!this.value){
		    		$('billing_address_book').show();
		    		}
		    	});
	    		
	    	}else if(element.id != 'billing_use_for_shipping_yes' && element.id != 'billing_taxvat' && element.id != 'buy_without_vat'){
	    	
		    	element.observe('change', function(e){
		    		$('billing_address_book').show();
		    	});
	    	
	    	}
	    	
	    });
	    
	    if(shipping_address_block = $('gcheckout-shipping-address')){
		    shipping_address_block.select('select, input').each(function(element){
		    	
		    	if(element.name == 'shipping_address_id'){
		    		
		    		element.observe('change', function(e){
		    			if(!this.value){
			    		$('shipping_address_book').show();
			    		}
			    	});
		    		
		    	}else{
		    	
			    	element.observe('change', function(e){
			    		$('shipping_address_book').show();
			    	});
		    	
		    	}
		    	
		    });
	    }
	    
	    }
    }
	
});
LightcheckoutLogin = Class.create({
	
	url:'',
	initialize:function(data){
		this.url = (data && data.url) ? data.url : '';
	},
	submit:function(params){
		
		$$('#gcheckout-login-form .actions button')[0].style.display = 'none';
		$$('#gcheckout-login-form .actions .loadinfo')[0].style.display = 'block';
		
		var request = new Ajax.Request(this.url,
		  {
		    method:'post',
		    parameters:{'login[username]':$$('#gcheckout-login-form #email')[0].value,'login[password]':$$('#gcheckout-login-form #pass')[0].value},
		    onSuccess: function(transport){
		    	
		    	
		    	
		    	try{
				eval('var response = '+transport.responseText);
				}catch(e){
					var response = new Object();
					response.error = true;
					response.message = 'Unknow error.';
				}
				if(!response.error){
					
					
					
					$$('.validation-advice').each(function(e){e.remove()});
					
					
					
					var form = $('gcheckout-onepage-form');
					
					form.innerHTML = response.content;
					
					
					
					if($$('.header .links').length && response.links){
					
					var tempelement = document.createElement('div');
					
					tempelement.innerHTML = response.links;
					
					var links = $$('.header .links')[0];
					
					links.parentNode.replaceChild(tempelement.firstChild, links);
					
					}
			    	$('gcheckout-login-link').hide();
			    	
			    	if(typeof initDeliveryDateCallendar != 'undefined'){
			    		initDeliveryDateCallendar();
			    	}
			    	
			    	
			    	checkout.billing_taxvat_verified_flag = response.vatstatus;
			    	checkout.hideLoginForm();
			    	this.customerIsCustomerLoggedIn = true;
			    	
			    	
			    	
			    	initAddresses();
			    	
			    	checkout.initialize();
			    	
			    	payment.init();
					
				}else{
					
					if($$('#gcheckout-login-form div.error').length == 0){
						$('gcheckout-login-form').insert({'top':'<div class="error"></div>'}	);
					}
					$$('#gcheckout-login-form div.error')[0].innerHTML = '';
					$$('#gcheckout-login-form div.error')[0].insert(response.message);
				}
				
				$$('#gcheckout-login-form .actions button')[0].style.display = 'block';
				$$('#gcheckout-login-form .actions .loadinfo')[0].style.display = 'none';
		    	
		    }.bind(this),
		    onFailure: function(){
		    	//...
		    }
		  });
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
        
        this.all_payment_methods = new Array();
                        
        for (var i=0; i<elements.length; i++) {
            if (elements[i].name=='payment[method]') {
                if (elements[i].checked) {
                    method = elements[i].value;
                }                
                this.all_payment_methods[this.all_payment_methods.length] = elements[i].value;                
                if (elements[i].value.indexOf('sagepay') >= 0)
                {
                	var sagepay_dt = elements[i].up('dt');
                	if (sagepay_dt)
                	{
                		sagepay_dt.addClassName('gcheckout-onepage-sagepay');
                	}	
                }	
            }
            elements[i].setAttribute('autocomplete','off');
        }
                        
        $$('#checkout-payment-method-load dd input, #checkout-payment-method-load dd select, #checkout-payment-method-load dd textarea').each(function(e){
    		
    		e.disabled = true;
    		
    	});
        
        if (method) this.switchMethod(method);
        
        this.afterInit(); 
    },
    
    switchMethod: function(method){
    	    	
        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
        	
        	$$('.validation-advice').each(function(e){
        		
        		e.hide();
        		
        	});
        	
        	$$('input.validation-failed, select.validation-failed, textarea.validation-failed').each(function(e){
        		
        		e.removeClassName('validation-failed');
        		
        	});
        	
        }
        
        for(var j=0; j<this.all_payment_methods.length; j++) {        	
        	if (this.all_payment_methods[j] && $('payment_form_'+this.all_payment_methods[j]))
        	{	
	        	var form = $('payment_form_'+this.all_payment_methods[j]);
	            form.style.display = 'none';
	            var elements = form.getElementsByTagName('input');	            
	            for (var i=0; i<elements.length; i++) elements[i].disabled = true;
	            var elements = form.getElementsByTagName('select');	            
	            for (var i=0; i<elements.length; i++) elements[i].disabled = true;
        	}
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
    },
    addAfterInitFunction : function(code, func) {
        this.afterInitFunc.set(code, func);
    },

    afterInit : function() {
        (this.afterInitFunc).each(function(init){
            (init.value)();
        });
    } 
}


Object.extend(RegionUpdater.prototype, {
 
    setMarkDisplay: function(elem, display){
        
    }
    
});

var LightcheckoutReview = Class.create();
LightcheckoutReview.prototype = {
    initialize: function(saveUrl, successUrl, agreementsForm){
        this.saveUrl = saveUrl;
        this.successUrl = successUrl;
        this.agreementsForm = agreementsForm;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);               
    },
    
    preparedata: function(){
    	checkout.existsreview = true;
    	checkout.submit(checkout.getFormData(), 'prepare_sagepay');    	
    },

    save: function(){
    	    	    	    	    	    	    	        
        var params = Form.serialize(payment.form);
        if (this.agreementsForm) {
            params += '&'+Form.serialize(this.agreementsForm);
        }
        params.save = true;
        var request = new Ajax.Request(
            this.saveUrl,
            {
                method:'post',
                parameters:params,
                onComplete: this.onComplete,
                onSuccess: this.onSave,
                onFailure: function(){
		    		//...
		    	}
            }
        );    	
    },

    resetLoadWaiting: function(transport){
        checkout.hideLoadinfo();
    },

    nextStep: function(transport){
    	
    	if (transport && transport.responseText) {
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
            if (response.redirect) {
                location.href = response.redirect;
                return;
            }
            if (response.success) {
                this.isSuccess = true;
                window.location=this.successUrl;
            }
            else{
                var msg = response.error_messages;
                if (typeof(msg)=='object') {
                    msg = msg.join("\n");
                }
                if (msg) {
                    alert(msg);
                }
            }
        }
    },

    isSuccess: false
};

var LightcheckoutglobalEval = function LightcheckoutglobalEval(src){
    if (window.execScript) {
        window.execScript(src);
        return;
    }
    var fn = function() {
        window.eval.call(window,src);
    };
    fn();
};