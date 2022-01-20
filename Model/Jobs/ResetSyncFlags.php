<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Jobs;

use Kimonix\Kimonix\Model\AbstractJobs;

class ResetSyncFlags extends AbstractJobs
{
    public function execute($entityType = null)
    {
        try {
            $resourceConnectionType = $entityType === 'orders' ? 'sales' : 'default';
            $resourceSyncFlagsTableName = $resourceConnectionType === 'default' ? 'kimonix_sync' : 'kimonix_sync_' . $resourceConnectionType;
            $this->_processOutput("ResetSyncFlags::execute() - (entity: {$entityType}) [STARTED]", "debug");
            $this->_resourceConnection->getConnection($resourceConnectionType)->update(
                $this->_resourceConnection->getTableName($resourceSyncFlagsTableName, $resourceConnectionType),
                ['sync_flag' => 0],
                (($entityType) ? ['entity_type = ?' => "{$entityType}"] : [])
            );
            $this->_processOutput("Kimonix - resetSyncFlags (entity: {$entityType}) [DONE]", "debug");
        } catch (\Exception $e) {
            $this->_processOutput("ResetSyncFlags::execute() - Exception:  " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
        }
    }
}
