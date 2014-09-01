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

function DeliveryDateNonworkingOrderBy($data, $field) { 
	$code = "return strnatcmp(\$a['$field'], \$b['$field']);"; 
	usort($data, create_function('$a,$b', $code)); 
	return $data; 
} 

class GoMage_DeliveryDate_Model_Adminhtml_System_Config_Backend_Design_Nonworking extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{    
 	protected function _beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
            if (count($value)){            	            	
            	$value = DeliveryDateNonworkingOrderBy($value, 'sort');
            	$keys = array();
            	for($i=0; $i < count($value); $i++){
            		$keys[] = 'nonworking_' . uniqid();
            	}   
				$value = array_combine($keys, array_values($value));            	
            }
        }
        $this->setValue($value);

        parent::_beforeSave();
    }
    
}
