<?php
/**
 * Mapping collection
 *
 * @category    Mageinn
 * @package     Mageinn_Advusers
 */
class Mageinn_Advusers_Model_Resource_Mapping_Collection 
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('mageinn_advusers/mapping');
    }
}
