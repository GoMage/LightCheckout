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
 * @since        Class available since Release 1.0
 */
	
class GoMage_DeliveryDate_Model_Adminhtml_System_Config_Source_Deliverydays{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('gomage_checkout')->__('None')),
            array('value' => 1, 'label'=>'1'),
            array('value' => 2, 'label'=>'2'),
            array('value' => 3, 'label'=>'3'),
            array('value' => 4, 'label'=>'4'),
            array('value' => 5, 'label'=>'5'),
            array('value' => 6, 'label'=>'6'),
            array('value' => 7, 'label'=>'7'),
            array('value' => 8, 'label'=>'8'),
            array('value' => 9, 'label'=>'9'),
            array('value' => 10, 'label'=>'10'),
            array('value' => 11, 'label'=>'11'),
            array('value' => 12, 'label'=>'12'),
            array('value' => 13, 'label'=>'13'),
            array('value' => 14, 'label'=>'14'),
        );
    }

}