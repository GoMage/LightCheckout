<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 4.1
 * @since        Class available since Release 2.4
 */ 

class GoMage_Checkout_Block_Onepage_Poll extends Mage_Core_Block_Template
{   
    protected $_voted;
     
    public function __construct()
    {
        parent::__construct();

        $this->_voted = true;
        
        $pollModel = Mage::getModel('poll/poll');
        $pollId = Mage::getSingleton('core/session')->getJustVotedPoll();
        if (empty($pollId)) {
            $votedIds = $pollModel->getVotedPollsIds();
            if (!count($votedIds)) $votedIds = array(0);
            
            $pollIds = $pollModel->getCollection()
                                 ->addStoreFilter(Mage::app()->getStore()->getId())                                    
                                 ->addFieldToFilter("main_table.poll_id", array("attribute"=>"poll_id", "nin"=>$votedIds))
                                 ->getAllIds();
                                 
            if (in_array(Mage::helper('gomage_checkout')->getConfigData('poll_settings/question'), $pollIds)){
                $pollId = Mage::helper('gomage_checkout')->getConfigData('poll_settings/question');
            }                     
        }
        
        if (empty($pollId)) {
            return false;
        }        

        $poll = $pollModel->load($pollId);
        $pollAnswers = Mage::getModel('poll/poll_answer')
            ->getResourceCollection()
            ->addPollFilter($pollId)
            ->load()
            ->countPercent($poll);
        
        $this->assign('poll', $poll)
             ->assign('poll_answers', $pollAnswers);

        $this->_voted = Mage::getModel('poll/poll')->isVoted($pollId);
        Mage::getSingleton('core/session')->setJustVotedPoll(false); 
        
    }
    
    protected function _toHtml()
    {
        if( $this->_voted === true ) {
            return '';
        } else {
            return parent::_toHtml();
        }        
    } 

}
