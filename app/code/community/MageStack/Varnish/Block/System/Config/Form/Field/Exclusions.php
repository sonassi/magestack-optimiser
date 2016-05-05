<?php
/*
* @category    Module
* @package     MageStack_Varnish
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Varnish_Block_System_Config_Form_Field_Exclusions extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('regexp', array(
            'label' => Mage::helper('adminhtml')->__('Matched URI'),
            'style' => 'width:120px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Exception');

        parent::__construct();
    }
}
