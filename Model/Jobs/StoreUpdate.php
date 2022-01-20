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
use Kimonix\Kimonix\Model\Request\Factory as KimonixRequestFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class StoreUpdate extends AbstractJobs
{
    public function execute()
    {
        try {
            $this->emulateAdminArea();
            if (!$this->_kimonixConfig->isEnabled()) {
                $this->_processOutput("StoreUpdate::execute() - Kimonix is disabled [SKIPPING]", "debug");
                return $this;
            }
            $this->_processOutput("StoreUpdate::execute() - Updating store config...", "debug");
            $this->getRequestFactory()
                ->create(KimonixRequestFactory::SETUP_STORE_REQUEST_METHOD)
                ->execute($this->getOutput())
                ->update(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $this->_kimonixConfig->getDefaultStoreId());
            $this->_processOutput("StoreUpdate::execute() - Updating store config [SUCCESS]", "debug");
        } catch (\Exception $e) {
            $this->_processOutput("StoreUpdate::execute() - Exception:  " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
        }
        $this->stopEnvironmentEmulation();
        return $this;
    }
}
