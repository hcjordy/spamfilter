<?php
/**
 * Created by PhpStorm.
 * User: martijn
 * Date: 07-12-17
 * Time: 09:51
 */

class HC_Spamfilter_Block_Config_BlockedDomain extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {

    protected $_countries;

    public function _prepareToRender()
    {
       $this->addColumn('domain', array(
            'label' => $this->helper('spamfilter')->__('Blocked Domain'),
        ));


        $this->_addAfter = false;
        $this->_addButtonLabel = $this->helper('spamfilter')->__('Add');
    }


}