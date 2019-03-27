<?php
/**
 * Mageinn_Advusers extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mageinn
 * @package     Mageinn_Advusers
 * @copyright   Copyright (c) 2016 Mageinn. (http://mageinn.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Mapping in category grid
 *
 * @category    Mageinn
 * @package     Mageinn_Advusers
 * @author      Mageinn
 */
class Mageinn_Advusers_Block_Adminhtml_Catalog_Category_Tab_Mapping 
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('catalog_category_mapping');
        $this->setDefaultSort('mapping_id');
        $this->setUseAjax(true);
    }

    public function getCategory()
    {
        return Mage::registry('category');
    }

    protected function _prepareCollection()
    {
        $model = Mage::getModel('mageinn_advusers/mapping');
        $collection = $model->getCollection()
                ->join(array('user' => 'admin/user'), 
                    'user.user_id = main_table.user_id', '*');
        
        if ($this->getCategory()->getId()) {
            $collection->addFieldToFilter('category_id', $this->getCategory()->getId());
        } else {
            $collection->addFieldToFilter('category_id', '');
        }
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('mapping_id',
            array(
                'header'=> Mage::helper('mageinn_advusers')->__('Mapping Id'),
                'width' => '20px',
                'sortable'  => false,
                'index'     => 'mapping_id',
        ));
        $this->addColumn('user_id',
            array(
                'header'=> Mage::helper('mageinn_advusers')->__('User Id'),
                'width' => '20px',
                'sortable'  => false,
                'index'     => 'user_id',
        ));
        $this->addColumn('lastname',
            array(
                'header'=> Mage::helper('mageinn_advusers')->__('Last Name'),
                'width' => '100px',
                'sortable'  => false,
                'index'     => 'lastname',
        ));
        $this->addColumn('firstname',
            array(
                'header'=> Mage::helper('mageinn_advusers')->__('First Name'),
                'width' => '100px',
                'sortable'  => false,
                'index'     => 'firstname',
        ));
        
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('advusersadmin/adminhtml_mapping/edit', array('id' => $row->getId()));
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('advusersadmin/adminhtml_mapping/grid', array('_current' => true));
    }
}