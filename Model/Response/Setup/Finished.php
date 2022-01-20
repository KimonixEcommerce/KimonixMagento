<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Response\Setup;

use Kimonix\Kimonix\Model\AbstractResponse;

/**
 * Kimonix setup finished response model.
 */
class Finished extends AbstractResponse
{
    /**
     * @method update
     * @param  string                $scope Scope
     * @param  int|null              $scopeId
     * @return AbstractResponse
     */
    public function update($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        $this->_kimonixConfig->updateIsSetupFinished(1, $scope, $scopeId);
        return $this;
    }
}
