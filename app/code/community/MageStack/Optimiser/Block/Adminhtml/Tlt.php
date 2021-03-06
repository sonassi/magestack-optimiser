<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Block_Adminhtml_Tlt extends MageStack_Optimiser_Block_Adminhtml_List_Abstract
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('magestack_optimiser')->__('Total Load Time');
    }

    public function getCategory()
    {
        return 'tlt';
    }
}
