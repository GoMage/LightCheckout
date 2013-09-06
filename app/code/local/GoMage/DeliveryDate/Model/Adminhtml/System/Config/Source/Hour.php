<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.4
 * @since        Class available since Release 1.0
 */
	
class GoMage_DeliveryDate_Model_Adminhtml_System_Config_Source_Hour{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label'=>'00:00'),
            array('value' => '1', 'label'=>'01:00'),
            array('value' => '2', 'label'=>'02:00'),
            array('value' => '3', 'label'=>'03:00'),
            array('value' => '4', 'label'=>'04:00'),
            array('value' => '5', 'label'=>'05:00'),
            array('value' => '6', 'label'=>'06:00'),
            array('value' => '7', 'label'=>'07:00'),
            array('value' => '8', 'label'=>'08:00'),
            array('value' => '9', 'label'=>'09:00'),
            array('value' => '10', 'label'=>'10:00'),
            array('value' => '11', 'label'=>'11:00'),
            array('value' => '12', 'label'=>'12:00'),
            array('value' => '13', 'label'=>'13:00'),
            array('value' => '14', 'label'=>'14:00'),
            array('value' => '15', 'label'=>'15:00'),
            array('value' => '16', 'label'=>'16:00'),
            array('value' => '17', 'label'=>'17:00'),
            array('value' => '18', 'label'=>'18:00'),
            array('value' => '19', 'label'=>'19:00'),
            array('value' => '20', 'label'=>'20:00'),
            array('value' => '21', 'label'=>'21:00'),
            array('value' => '22', 'label'=>'22:00'),
            array('value' => '23', 'label'=>'23:00'),
        );
    }

}