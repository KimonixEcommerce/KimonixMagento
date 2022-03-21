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
 * Kimonix store response model.
 */
class Store extends AbstractResponse
{
    /**
     * @var string
     */
    protected $_kimonixStoreId;

    /**
     * @var int
     */
    protected $_allowDataSending;

    /**
     * @var int
     */
    protected $_dataPeriodDays;

    /**
     * @return AbstractResponse
     */
    public function process($output = null)
    {
        parent::process($output);

        $body = $this->getInnerBody();
        $this->_kimonixStoreId = (string) $body['_id'];
        $this->_allowDataSending = $body['allow_data_sending'] ? 1 : 0;
        $this->_dataPeriodDays = (int) $body['data_period_days'];

        return $this;
    }

    protected function getInnerBodyKey()
    {
        return 'store';
    }

    /**
     * @return array
     */
    protected function getRequiredResponseDataKeys()
    {
        return ['_id', 'allow_data_sending', 'data_period_days'];
    }

    /**
     * @method update
     * @param  string                $scope Scope
     * @param  int|null              $scopeId
     * @return AbstractResponse
     */
    public function update($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        $clearCache = false;
        if ($this->getKimonixStoreId() !== $this->_kimonixConfig->getKimonixStoreId()) {
            $this->_kimonixConfig->resetStoreConfig($scope, $scopeId);
            $this->_kimonixConfig->updateKimonixStoreId($this->getKimonixStoreId(), $scope, $scopeId);
            $clearCache = true;
        }
        if ((bool) $this->getAllowDataSending() !== (bool) $this->_kimonixConfig->getAllowDataSending()) {
            $this->_kimonixConfig->updateAllowDataSending($this->getAllowDataSending(), $scope, $scopeId);
            $clearCache = true;
        }
        if ((string) $this->getDataPeriodDays() !== (string) $this->_kimonixConfig->getDataPeriodDays()) {
            $this->_kimonixConfig->updateDataPeriodDays($this->getDataPeriodDays(), $scope, $scopeId);
            $clearCache = true;
        }
        if ($clearCache) {
            $this->cleanConfigCache();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getKimonixStoreId()
    {
        return $this->_kimonixStoreId;
    }

    /**
     * @return int
     */
    public function getAllowDataSending()
    {
        return $this->_allowDataSending;
    }

    /**
     * @return int
     */
    public function getDataPeriodDays()
    {
        return $this->_dataPeriodDays;
    }
}
