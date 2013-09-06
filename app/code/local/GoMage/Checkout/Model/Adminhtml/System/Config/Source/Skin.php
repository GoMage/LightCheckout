<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.0
 * @since        Class available since Release 1.0
 */
	
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Skin{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'default', 'label'=>'Custom'),
            array('value' => 'black', 'label'=>'Black'),
            array('value' => 'blue', 'label'=>'Blue'),
            array('value' => 'brown', 'label'=>'Brown'),
            array('value' => 'gray', 'label'=>'Gray'),
            array('value' => 'green', 'label'=>'Green'),
            array('value' => 'light-blue', 'label'=>'Light-Blue'),
            array('value' => 'orange', 'label'=>'Orange'),
            array('value' => 'red', 'label'=>'Red'),
            array('value' => 'pink', 'label'=>'Pink'),
            array('value' => 'violet', 'label'=>'Violet'),
            array('value' => 'yellow', 'label'=>'Yellow'),
        );
    }

}