<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model;

use Magento\Framework\Model\AbstractModel;

class SyncSales extends AbstractModel
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init(\Kimonix\Kimonix\Model\ResourceModel\SyncSales::class);
    }
}
