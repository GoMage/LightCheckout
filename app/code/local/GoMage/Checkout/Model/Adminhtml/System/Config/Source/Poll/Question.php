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
 * @since        Class available since Release 2.4
 */
	
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Poll_Question{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = array();
        $data[] = array('value' => '', 'label'=>'');
        $collection = Mage::getModel('poll/poll')->getCollection();
        
        foreach ($collection as $poll){
            if (!$poll->getData('closed'))
                $data[] = array('value' => $poll->getData('poll_id'), 'label'=> $poll->getData('poll_title'));
        }
        
        return $data;
    }

}