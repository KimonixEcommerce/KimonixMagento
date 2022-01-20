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
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Lib\Http\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * Kimonix store response model.
 */
class Store extends AbstractResponse
{
    /**
     * @var ReinitableConfigInterface
     */
    private $_appConfig;

    /**
     * @var TypeListInterface
     */
    private $_cacheTypeList;

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
     * @method __construct
     * @param  KimonixConfig             $kimonixConfig
     * @param  Curl                      $curl
     * @param  ReinitableConfigInterface $appConfig
     * @param  TypeListInterface         $cacheTypeList
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        Curl $curl,
        ReinitableConfigInterface $appConfig,
        TypeListInterface $cacheTypeList
    ) {
        parent::__construct($kimonixConfig, $curl);
        $this->_appConfig = $appConfig;
        $this->_cacheTypeList = $cacheTypeList;
    }

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

    protected function cleanConfigCache()
    {
        try {
            $this->_cacheTypeList->cleanType(Config::TYPE_IDENTIFIER);
            $this->_appConfig->reinit();
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Kimonix changes are saved, but for some reason, it couldn\'t clear the config cache. Please clear the cache manually. (Exception message: %s)', $e->getMessage()));
        }
        return $this;
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
