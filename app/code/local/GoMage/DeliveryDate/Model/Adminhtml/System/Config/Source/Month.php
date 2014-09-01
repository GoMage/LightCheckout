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
 * @since        Class available since Release 2.5
 */
	
class GoMage_DeliveryDate_Model_Adminhtml_System_Config_Source_Month{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$helper = Mage::helper('gomage_deliverydate');
    	 
        return array(            
            array('value' => '0', 'label'=> $helper->__('January')),
            array('value' => '1', 'label'=> $helper->__('February')),
            array('value' => '2', 'label'=> $helper->__('March')),
            array('value' => '3', 'label'=> $helper->__('April')),
            array('value' => '4', 'label'=> $helper->__('May')),
            array('value' => '5', 'label'=> $helper->__('June')),
            array('value' => '6', 'label'=> $helper->__('July')),
            array('value' => '7', 'label'=> $helper->__('August')),
            array('value' => '8', 'label'=> $helper->__('September')),
            array('value' => '9', 'label'=> $helper->__('October')),
            array('value' => '10', 'label'=> $helper->__('November')),
            array('value' => '11', 'label'=> $helper->__('December')),            
        );
    }        

}