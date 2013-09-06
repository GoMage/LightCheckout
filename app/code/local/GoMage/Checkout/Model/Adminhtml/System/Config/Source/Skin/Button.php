<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 2.0
 */
	
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Skin_Button{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'square-black', 'label'=>'Square Black'),
            array('value' => 'square-blue', 'label'=>'Square Blue'),
            array('value' => 'square-brown', 'label'=>'Square Brown'),
            array('value' => 'square-grey', 'label'=>'Square Grey'),
            array('value' => 'square-green', 'label'=>'Square Green'),
            array('value' => 'square-light-blue', 'label'=>'Square Light-Blue'),
            array('value' => 'square-light-green', 'label'=>'Square Light-Green'),
            array('value' => 'square-orange', 'label'=>'Square Orange'),
            array('value' => 'square-red', 'label'=>'Square Red'),
            array('value' => 'square-pink', 'label'=>'Square Pink'),
            array('value' => 'square-violet', 'label'=>'Square Violet'),
            array('value' => 'square-yellow', 'label'=>'Square Yellow'),
            array('value' => 'round-black', 'label'=>'Round Black'),
            array('value' => 'round-blue', 'label'=>'Round Blue'),
            array('value' => 'round-gray', 'label'=>'Round Gray'),
            array('value' => 'round-green', 'label'=>'Round Green'),
            array('value' => 'round-orange', 'label'=>'Round Orange'),
            array('value' => 'round-red', 'label'=>'Round Red'),
                       
        );
    }

}