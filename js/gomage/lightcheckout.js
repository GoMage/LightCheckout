/**
 * GoMage LightCheckout Extension
 * 
 * @category Extension
 * @copyright Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author GoMage
 * @license http://www.gomage.com/license-agreement/ Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version Release: 5.1
 * @since Class available since Release 1.0
 */ 

Lightcheckout = Class.create({
	billing_taxvat_enabled:false,
	billing_taxvat_verified_flag:false,
	url:'',
	save_order_url:'',
	existsreview: false,
	disable_place_order: false,
	accordion: null,
	exists_customer: false,
	initialize:function(data){

        if (typeof Accordion != 'undefined'){
		    this.accordion = new Accordion('checkout-review-submit', '.step-title', true);
        }

		if(data && (typeof data.billing_taxvat_enabled != 'undefined')){
			this.billing_taxvat_enabled = data.billing_taxvat_enabled;
		}

		(data && data.url) ? this.url = data.url : '';
		(data && data.save_order_url) ? this.save_order_url = data.save_order_url : '';

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
		
		$$('#billing_email, #customer_account_create').each(function(e){			
			e.observe('change', function(e){
				this.obj.findExistsCustomer();				
			}.bind({obj:this}));
		}.bind(this));

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
    	
    	if ($('use_customer_balance')) {
    		$('use_customer_balance').observe('click', function(e){
        		this.submit(this.getFormData(), 'get_totals');
        	}.bind(this));
    	}
    	    	
		this.observeMethods();
		this.observeAddresses();
		this.findExistsCustomer();
		this.setBlocksNumber();
	},
	
	findExistsCustomer: function(){
		
		if (!$('billing_email') ||
			($('customer_account_create') && !$('customer_account_create').checked)){
			this.exists_customer = false;
			return false;
		}
		
		var email = $('billing_email').value;
		var glc_self = this;
		if (email){
			var params = {email : email};
			params.action = 'find_exists_customer';
			var request = new Ajax.Request(this.url,
			  {
			    method:'post',
			    parameters:params,
			    onSuccess: function(transport){
			    	eval('var response = '+transport.responseText);
			    	glc_self.exists_customer = response.exists;			    	
			    }
			  });
			
		}
	},

	prepareDeliveryDate: function(){
    	var control = $$('div.shipping-advanced')[0];
		if (control){
			var wrap = $$('div.gcheckout-onepage-wrap')[0];
			var elements = $$('#gcheckout-onepage-form input[name=shipping_method]');
			var method = '';
			var show = false;

			for(var i = 0;i < elements.length;i++){
				if(((elements[i].type == 'checkbox') || elements[i].type == 'radio') && !elements[i].checked){
					continue;
				}
				if (elements[i].disabled){
					continue;
				}
				if (elements[i].checked){
					method = elements[i].value;
					break;
				}
			}
			
			if (method != ''){
				if (glc_dilivery_date_shipping_methods.indexOf(method) >= 0){
					show = true;
				}else{
					for (key in glc_dilivery_date_shipping_methods){
						if (method.indexOf(glc_dilivery_date_shipping_methods[key]) == 0){
							show = true;
							break;
						}
					}
				}				
			}

			if (show){
				control.show();
				wrap.removeClassName('not_deliverydate_mode');				
			}else{
				control.hide();
				wrap.addClassName('not_deliverydate_mode');
			}
			this.setBlocksNumber();
		}
	},

	observeMethods:function(){		
		this.observeShippingMethods();
		this.observePaymentMethods();		
	},

	observePaymentMethods:function(){
		
		var payment_elms = $$('#checkout-payment-method-load input[name="payment[method]"]');

		payment_elms.each(function(e){
			e.addClassName('validate-one-required-by-name');
			throw $break;
		});

		var payment_method_selected = false;
		payment_elms.each(function(e){
			if(e.checked){
				payment_method_selected = true;
				payment.switchMethod(e.value);				
				throw $break;
			}
		});

		if(default_payment_method && !payment_method_selected && (input = $$('#gcheckout-payment-methods input[value="'+default_payment_method+'"]')[0])){
			input.checked = true;
			payment.switchMethod(input.value);
		}
		
		$$('#checkout-payment-method-load input[name="payment[method]"]').each(function(e){
			Event.stopObserving(e, 'click');
			e.observe('click', function(e){
				if(this.elem.checked){
					if (default_payment_method)
						default_payment_method = this.elem.value;
					this.obj.submit(this.obj.getFormData(), 'get_totals');
				}
			}.bind({elem:e,obj:this}));
		}.bind(this));
		
        $$('.cvv-what-is-this, #payment-tool-tip-close').each(function(element){
            Event.observe(element, 'click', toggleToolTip);
        });
        
	},
	
	observeShippingMethods:function(){
		$$('#gcheckout-shipping-method-available input').each(function(e){
			e.onchange = null;
			Event.stopObserving(e, 'click');
			e.observe('click', function(e){
				if(this.elem.checked){
					this.obj.prepareDeliveryDate();					
					this.obj.submit(this.obj.getFormData(), 'get_totals');
				}
			}.bind({elem:e,obj:this}));
		}.bind(this));
		$$('#gcheckout-onepage-form input[name=shipping_method]').each(function(e){
			e.addClassName('validate-one-required-by-name');
			throw $break;
		});
		
		this.prepareDeliveryDate();
	},	

	setBlocksNumber:function(){
		var number = 2;
		if ($('gcheckout-shipping-address') && $('gcheckout-shipping-address').visible()){
			number++;
		}
		var shipping_block = ($$('.shipping-method').length > 0 ? $$('.shipping-method')[0] : false);
		if (shipping_block && shipping_block.visible()){
			$('glc-shipping-number').innerHTML = number;
			number++;
		}
        var deliverydate_block = ($$('.shipping-advanced').length > 0 ? $$('.shipping-advanced')[0] : false);
		if (deliverydate_block && deliverydate_block.visible()){
			$('glc-deliverydate-number').innerHTML = number;
			number++;
		}
		if ($('gcheckout-payment-methods') && $('gcheckout-payment-methods').visible()){
			$('glc-payment-number').innerHTML = number;			
		}
	},
	
	getFormData:function(){		
		var form_data = $('gcheckout-onepage-form').serialize(true);
		for (var key in form_data){
		    if ((key == 'billing[customer_password]') || (key == 'billing[confirm_password]')){
		    	form_data[key] = GlcUrl.encode(form_data[key]);
		    }
		    if(payment.currentMethod == 'authorizenet_directpost') {		 		 
			    if (key.indexOf('payment[') == 0 && key != 'payment[method]' && key != 'payment[use_customer_balance]'){
			    	delete form_data[key];
			    }
		    }
		}
		
		return form_data;
	},
	
	applyDisocunt: function(flag){
		if (flag){
	        $('remove_coupone').value = 1;
			this.submit({coupon_code: GlcUrl.encode($('coupon_code').value),
						 remove: $('remove_coupone').value}, 'discount');
		}else{
	        $('remove_coupone').value = 0;
			this.submit({coupon_code: GlcUrl.encode($('coupon_code').value),
				         remove: $('remove_coupone').value}, 'discount');
		}
	},
	
	applyGiftCard: function(){		
		this.submit({giftcard_code: GlcUrl.encode($('giftcard_code').value)}, 'giftcard');		
	},
	
	removeGiftCard: function(giftcard_code){		
		this.submit({giftcard_code: giftcard_code, remove: 1}, 'giftcard');		
	},
	
	removeCustomerBalance: function(){
		$('use_customer_balance').checked = false;
		this.submit({}, 'customerbalance');		
	},

	LightcheckoutSubmit: function(){

		if(payment.currentMethod && (payment.currentMethod.indexOf('sagepay') == 0) &&
		   (SageServer != undefined) && (review != undefined))
		{
			if (checkoutForm.validator.validate()){
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

	saveorder:function(){

		this.showLoadinfo();

		var params = this.getFormData();

		var request = new Ajax.Request(this.save_order_url,
		{
		    method:'post',
		    parameters: params,
		    onSuccess: function(transport){
		    	eval('var response = '+transport.responseText);

		    	if(response.redirect){
			    	setLocation(response.redirect);
			    }else if(response.error){
					if(response.message){
						alert(response.message);
					}
				}else if (response.update_section){
					this.accordion.currentSection = 'opc-review';
					this.innerHTMLwithScripts($('checkout-update-section'), response.update_section.html);

				}
		    	this.hideLoadinfo();

			}.bind(this),
		    onFailure: function(){

		    }
		});

	},

	setLoadWaiting: function(step, keepDisabled) {
		return false;
	},

	submit:function(params, action){

		this.showLoadinfo();
		
		params.action = action;

		var request = new Ajax.Request(this.url,
		  {
		    method:'post',
		    parameters:params,
		    onSuccess: function(transport){

		    eval('var response = '+transport.responseText);

		    if(response.messages_block){
		    	var gcheckout_onepage_wrap = $$('div.gcheckout-onepage-wrap')[0];
		    	if (gcheckout_onepage_wrap){
		    		new Insertion.Before(gcheckout_onepage_wrap, response.messages_block);
		    	}
		    	this.disable_place_order = true;
		    }else{
		    	this.disable_place_order = false;
		    }

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

				var process_save_order = false;
				
				if(response.methods){
					// Quote isVirtual
					this.innerHTMLwithScripts($('gcheckout-onepage-methods'), response.methods);
					var wrap = $$('div.gcheckout-onepage-wrap')[0];
					if (wrap && !wrap.hasClassName('not_shipping_mode')){
						wrap.addClassName('not_shipping_mode');	
					}
					if ($('billing_use_for_shipping_yes') && $('billing_use_for_shipping_yes').up('li.control')){
						$('billing_use_for_shipping_yes').up('li.control').remove();
					}
					if ($('gcheckout-shipping-address')){
						$('gcheckout-shipping-address').remove();
					}
					payment.init();
					this.observeMethods();
				}
				
				if(response.shippings){
					if(shipping_rates_block = $('gcheckout-shipping-method-available')){
						this.innerHTMLwithScripts(shipping_rates_block, response.shippings);
						this.observeShippingMethods();
					}
				}

				if(response.payments){
					this.innerHTMLwithScripts($('gcheckout-payment-methods-available'), response.payments);
					payment.init();
					this.observePaymentMethods();
				}

				if (response.gift_message){
					if(giftmessage_block = $('gomage-lightcheckout-giftmessage')){
						this.innerHTMLwithScripts(giftmessage_block, response.gift_message);
					}
				}

				if(response.toplinks){						
					this.replaceTopLinks(response.toplinks);						
				}

                if(response.cart_sidebar && typeof(GomageProcartConfig) != 'undefined'){
                    GomageProcartConfig._replaceEnterpriseTopCart(response.cart_sidebar, ($('topCartContent') && $('topCartContent').visible()));
                }

				if(response.review){
					this.innerHTMLwithScripts($$('#gcheckout-onepage-review div.totals')[0], response.review);
				}

				if (response.content_billing){
					var div_billing = document.createElement('div');
					div_billing.innerHTML = response.content_billing;
					$('gcheckout-onepage-address').replaceChild(div_billing.firstChild, $('gcheckout-billing-address'));
				}

				if (response.content_shipping && $('gcheckout-shipping-address')){
					var div_shipping = document.createElement('div');
					div_shipping.innerHTML = response.content_shipping;
					$('gcheckout-onepage-address').replaceChild(div_shipping.firstChild, $('gcheckout-shipping-address'));
				}

				if (response.content_billing || response.content_shipping){
					initAddresses();
				}

				if(response.section == 'varify_taxvat'){
										
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

				}

				if (response.section == 'centinel'){

					if (response.centinel){
						this.showCentinel(response.centinel);
					}else{
						process_save_order = true;
						if((payment.currentMethod == 'authorizenet_directpost') && ((typeof directPostModel != 'undefined'))){
							directPostModel.saveOnepageOrder();
						}else{
							this.saveorder();
						}
					}
				}

                this.setBlocksNumber();

				if (this.existsreview)
				{
					this.existsreview = false;
					review.save();
				}
				else
				{
					if (!process_save_order){
						this.hideLoadinfo();
					}
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
	
	replaceTopLinks:function(toplinks){

		var link = $$('ul.links a.top-link-cart')[0];
		var link_class = '';
		if (link){
			link_class = 'top-link-cart';		
		}else{
			// enterprise
			var link = $$('div.quick-access div.top-cart')[0];
			link_class = 'top-cart';
		}
		
		if (link && link_class){
			var js_scripts = toplinks.extractScripts();																
			var content = toplinks;								
			if (content && content.toElement){
		    	content = content.toElement();
		    }else if (!Object.isElement(content)){
			    content = Object.toHTML(content);
			    var tempElement = document.createElement('div');
			    tempElement.innerHTML = content;
			    el =  this.getElementsByClassName(link_class, tempElement);
			    if (el.length > 0){
			        content = el[0];
			    }
			    else{
			       return;
			    }
		    }
			link.parentNode.replaceChild(content, link);
			for (var i=0; i< js_scripts.length; i++){
		        if (typeof(js_scripts[i]) != 'undefined'){
		        	LightcheckoutglobalEval(js_scripts[i]);
		        }
		    }
		}
		
	},

	showLoadinfo:function(){

		$('submit-btn').disabled = 'disabled';
		$('submit-btn').addClassName('disabled');

		$$('.validation-advice').each(function(e){e.remove()});

		msgs = $$('ul.messages');

		if(msgs.length){

			for(i = 0; i < msgs.length;i++){
				msgs[i].remove();
			}
		}

		$$('div.gcheckout-onepage-wrap')[0].insert('<div class="loadinfo">'+loadinfo_text+'</div>');
	},
	hideLoadinfo:function(){

		var e = $$('div.gcheckout-onepage-wrap .loadinfo')[0];

		e.parentNode.removeChild(e);

		if (!this.disable_place_order){
			$('submit-btn').disabled = false;
			$('submit-btn').removeClassName('disabled');
		}

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

		var left = loginForm.offsetWidth/2;

		var contentHeight = document.documentElement ? document.documentElement.clientHeight : document.body.clientHeigh;

		var top	= contentHeight/2.4 - loginForm.offsetHeight/2;

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

		// var scrolloffset = document.body.cumulativeScrollOffset();

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
    
	showCentinel:function(html){
		var overlay = this.showOverlay();

		overlay.onclick = function(){
			this.hideCentinel();
		}.bind(this);

		var centinel = $('gcheckout-payment-centinel');

		if(!centinel){
			$$('body')[0].insert(centinelHtml);
			var centinel = $('gcheckout-payment-centinel');
		}

        this.innerHTMLwithScripts($('gcheckout-payment-centinel-html'), html);

		centinel.style.position = 'fixed';
		centinel.style.display = 'block';

		var left	= centinel.offsetWidth/2;

		var contentHeight = document.documentElement ? document.documentElement.clientHeight : document.body.clientHeigh;

		var top		= contentHeight/2.4 - centinel.offsetHeight/2;

		centinel.style.left = '50%';
		centinel.style.marginLeft = '-'+left+'px';
		centinel.style.top	= top	+'px';
	},
	
	hideCentinel:function(){
		this.hideOverlay();
		var Form = $('gcheckout-payment-centinel');
		if(Form){
			Form.hide();
		}
	},
	
    loadAddress:function(type, id, url){

    	if(id){
			this.showLoadinfo();

			var use_for_shipping = ($('billing_use_for_shipping_yes') && $('billing_use_for_shipping_yes').checked);
			
			var request = new Ajax.Request(url,
			  {
			    method:'post',
			    parameters:{'id':id,
				            'type':type,
				            'use_for_shipping': use_for_shipping
				           },
			    onSuccess: function(transport){

			    eval('var response = '+transport.responseText);

			    if(response.error){



				}else{

					if(response.shippings){
						if ($('gcheckout-shipping-method-available')){
							this.innerHTMLwithScripts($('gcheckout-shipping-method-available'), response.shippings);
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

					if (response.content_shipping && $('gcheckout-shipping-address')){
						var div_shipping = document.createElement('div');
						div_shipping.innerHTML = response.content_shipping;
						$('gcheckout-onepage-address').replaceChild(div_shipping.firstChild, $('gcheckout-shipping-address'));
					}

					if(response.review){
						this.innerHTMLwithScripts($$('#gcheckout-onepage-review div.totals')[0], response.review);
					}

					initAddresses();

					checkout.initialize();
				}
				this.hideLoadinfo();

			    }.bind(this),
			    onFailure: function(){
			    	// ...
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
	url_forgot:'',
	initialize:function(data){
		this.url = (data && data.url) ? data.url : '';
		this.url_forgot = (data && data.url_forgot) ? data.url_forgot : '';
	},
	submit:function(params){
					
		if (typeof params != 'undefined' && params == 0){
			return true;
		}

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

					var content = response.content;					
					var js_scripts = content.extractScripts();
					
					form.innerHTML = content.stripScripts();

					if($$('.header .links').length && response.links){
						var tempelement = document.createElement('div');	
						tempelement.innerHTML = response.links;	
						var links = $$('.header .links')[0];	
						links.parentNode.replaceChild(tempelement.firstChild, links);
					}
					
					if (response.header && $$('.header-container').length){
						// enterprise
						var element = $$('.header-container')[0];
						var js_header_scripts = response.header.extractScripts();
						
						var tempelement = document.createElement('div');	
						tempelement.innerHTML = response.header.stripScripts();	
						
						element.parentNode.replaceChild(tempelement.firstChild, element);
												
						for (var i=0; i< js_header_scripts.length; i++){
					        if (typeof(js_header_scripts[i]) != 'undefined'){
					        	LightcheckoutglobalEval(js_header_scripts[i]);
					        }
					    }
					}
					
					if (!response.is_virtual){
						var wrap = $$('div.gcheckout-onepage-wrap')[0];
						if (wrap && wrap.hasClassName('not_shipping_mode')){
							wrap.removeClassName('not_shipping_mode');
						}
					}
					
			    	$('gcheckout-login-link').hide();
			    	
			    	for (var i=0; i< js_scripts.length; i++){
				        if (typeof(js_scripts[i]) != 'undefined'){				        	
				        	LightcheckoutglobalEval(js_scripts[i]);
				        }
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
		    	// ...
		    }
		  });
				
		return false;		
		
	},
	showForgotForm: function(){		
		if($$('#gcheckout-forgot-form div.error').length){
			$$('#gcheckout-forgot-form div.error')[0].style.display = 'none';
		}
		if($$('#gcheckout-forgot-form div.success').length){
			$$('#gcheckout-forgot-form div.success')[0].style.display = 'none';
		}
		$$('#gcheckout-forgot-form #forgot_email')[0].value = '';
		$('gcheckout-login-wrapper').style.display = 'none';
		$('gcheckout-forgot-wrapper').style.display = 'block';
	},
	showLoginForm: function(){
		$('gcheckout-forgot-wrapper').style.display = 'none';
		$('gcheckout-login-wrapper').style.display = 'block';		
	},
	submitForgot: function(){
			
		$$('#gcheckout-forgot-form .actions button')[0].style.display = 'none';
		$$('#gcheckout-forgot-form .actions .loadinfo')[0].style.display = 'block';
		
		if($$('#gcheckout-forgot-form div.error').length){
			$$('#gcheckout-forgot-form div.error')[0].style.display = 'none';
		}
		if($$('#gcheckout-forgot-form div.success').length){
			$$('#gcheckout-forgot-form div.success')[0].style.display = 'none';
		}

		var request = new Ajax.Request(this.url_forgot,
		  {
		    method:'post',
		    parameters:{'email':$$('#gcheckout-forgot-form #forgot_email')[0].value},
		    onSuccess: function(transport){
		    	try{
		    		eval('var response = '+transport.responseText);
				}catch(e){
					var response = new Object();
					response.error = true;
					response.message = 'Unknow error.';
				}
				if(!response.error){
					if($$('#gcheckout-forgot-form div.success').length == 0){
						$('gcheckout-forgot-form').insert({'top':'<div class="success"></div>'}	);
					}
					$$('#gcheckout-forgot-form div.success')[0].style.display = 'block';
					$$('#gcheckout-forgot-form div.success')[0].innerHTML = '';
					$$('#gcheckout-forgot-form div.success')[0].insert(response.message);
					
				}else{
					if($$('#gcheckout-forgot-form div.error').length == 0){
						$('gcheckout-forgot-form').insert({'top':'<div class="error"></div>'}	);
					}
					$$('#gcheckout-forgot-form div.error')[0].style.display = 'block';
					$$('#gcheckout-forgot-form div.error')[0].innerHTML = '';
					$$('#gcheckout-forgot-form div.error')[0].insert(response.message);
				}

				$$('#gcheckout-forgot-form .actions button')[0].style.display = 'block';
				$$('#gcheckout-forgot-form .actions .loadinfo')[0].style.display = 'none';
		    }.bind(this),
		    onFailure: function(){
		    	// ...
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

    	this.currentMethod = method;
    	
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

        if (this.currentMethod && $('payment_form_'+this.currentMethod)){
            var form = $('payment_form_'+this.currentMethod);
            form.style.display = '';
            var elements = form.getElementsByTagName('input');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;
            var elements = form.getElementsByTagName('select');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;            
        }        
    },
    addAfterInitFunction : function(code, func) {
        this.afterInitFunc.set(code, func);
    },

    afterInit : function() {
        (this.afterInitFunc).each(function(init){
            (init.value)();
        });
    },
    
    addBeforeValidateFunction : function(code, func) {
        this.beforeValidateFunc.set(code, func);
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
		    		// ...
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

var GlcUrl = {

		encode : function (string) {
			return escape(this._utf8_encode(string));
		},

		decode : function (string) {
			return this._utf8_decode(unescape(string));
		},

		_utf8_encode : function (string) {
			string = string.replace(/\r\n/g,"\n");
			var utftext = "";

			for (var n = 0; n < string.length; n++) {

				var c = string.charCodeAt(n);

				if (c < 128) {
					utftext += String.fromCharCode(c);
				}
				else if((c > 127) && (c < 2048)) {
					utftext += String.fromCharCode((c >> 6) | 192);
					utftext += String.fromCharCode((c & 63) | 128);
				}
				else {
					utftext += String.fromCharCode((c >> 12) | 224);
					utftext += String.fromCharCode(((c >> 6) & 63) | 128);
					utftext += String.fromCharCode((c & 63) | 128);
				}

			}

			return utftext;
		},

		_utf8_decode : function (utftext) {
			var string = "";
			var i = 0;
			var c = c1 = c2 = 0;

			while ( i < utftext.length ) {

				c = utftext.charCodeAt(i);

				if (c < 128) {
					string += String.fromCharCode(c);
					i++;
				}
				else if((c > 191) && (c < 224)) {
					c2 = utftext.charCodeAt(i+1);
					string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
					i += 2;
				}
				else {
					c2 = utftext.charCodeAt(i+1);
					c3 = utftext.charCodeAt(i+2);
					string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
					i += 3;
				}

			}

			return string;
		}
};

function toggleToolTip(event){
    if($('payment-tool-tip')){        
        $('payment-tool-tip').toggle();
    }
    Event.stop(event);
}