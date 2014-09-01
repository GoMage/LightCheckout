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
	
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Import extends Mage_Core_Model_Config_Data {
		
 	public function _afterSave()
    {
     	if (empty($_FILES['groups']['tmp_name']['geoip']['fields']['import']['value'])) {     		
            return $this;
        }
        
        $tmpPath = $_FILES['groups']['tmp_name']['geoip']['fields']['import']['value'];
		$destPath = Mage::getBaseDir('media') . DS . 'geoip' . DS;

		try {
			if(!file_exists($destPath)){
				mkdir($destPath);
				chmod($destPath, 0755);
			}					
			$destPath .= DS . 'GeoLiteCity.dat';			
			move_uploaded_file($tmpPath, $destPath);
		}
		catch (Exception $e){
			throw new Exception('File was not uploaded.');
		}
		        
    }
		
}