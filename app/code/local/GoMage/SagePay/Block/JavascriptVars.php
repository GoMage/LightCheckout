<?php

/**
 * JS vars
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */


if (Mage::helper('gomage_sagepay')->isGoMage_SagePayEnabled()) {

    class GoMage_SagePay_Block_JavascriptVars extends Ebizmarts_SagePaySuite_Block_JavascriptVars
    {
        public function __construct()
        {
            $this->setTemplate('sagepaysuite/payment/SagePaySuiteVars.phtml');
        	parent::__construct();
        } 
    }

}
else 
{
    class GoMage_SagePay_Block_JavascriptVars extends Mage_Core_Block_Template{
                        
    }
}