/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.0
 * @since        Class available since Release 3.1 
 */ 

PAYONE.Handler.CreditCardCheck.LightCheckout = function () {
    this.origMethod = '';

    this.haveToValidate = function () {
        var radio_p1_cc = $('p_method_payone_creditcard');
        if (radio_p1_cc != undefined && radio_p1_cc != null && radio_p1_cc.checked) {
            return 1;
        }
        return 0;
    };

    this.handleResponse = function (response) {
        if (response.status != 'VALID') {
            // Failure
            alert(response.customermessage);
            checkout.setLoadWaiting(false);
            return false;
        }
        else {
            // Success!
            var pseudocardpan = response.pseudocardpan;
            var truncatedcardpan = response.truncatedcardpan;

            $('payone_pseudocardpan').setValue(pseudocardpan);
            $('payone_truncatedcardpan').setValue(truncatedcardpan);
            $('payone_creditcard_cc_number').setValue(truncatedcardpan);

            cid = $('payone_creditcard_cc_cid');
            if (cid != undefined) {
                $('payone_creditcard_cc_cid').setValue('');
            }

            $('payone_creditcard_cc_number').removeClassName('validate-cc-number'); // adjustment for GOMAGE
            $('payone_creditcard_cc_number').removeClassName('validate-payone-cc-type'); // adjustment for GOMAGE
            $('payone_creditcard_cc_cid').removeClassName('required-entry'); // adjustment for GOMAGE
            this.origMethod(); // adjustment for GOMAGE
            return true;
        }
    };
};