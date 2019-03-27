<?php
/**
 * Mapping Resource Model
 *
 * @category    Mageinn
 * @package     Mageinn_Advusers
 */
class Mageinn_Advusers_Model_Resource_Mapping extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('mageinn_advusers/mapping', 'mapping_id');
    }
}