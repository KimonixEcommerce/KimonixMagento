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

use Kimonix\Kimonix\Model\Logger as KimonixLogger;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Review\Model\Review\Config as ReviewConfig;

/**
 * Kimonix config model.
 */
class Config
{
    public const MODULE_NAME = 'Kimonix_Kimonix';

    public const KIMONIX_DATE_FORMAT = 'Y-m-d\TH:i:s.000\Z';

    public const KIMONIX_API_URL = 'https://api-magento.kimonix.com'; //Default
    public const CONFIGPATH_KIMONIX_API_URL = 'kimonix/general_settings/kimonix_api_url'; //Override

    public const CONFIGPATH_KIMONIX_ENABLED = 'kimonix/general_settings/enabled';
    public const CONFIGPATH_KIMONIX_DEBUG = 'kimonix/general_settings/debug';
    public const CONFIGPATH_KIMONIX_API_KEY = 'kimonix/general_settings/kimonix_api_key';
    public const CONFIGPATH_KIMONIX_ORDERS_SYNC_LIMIT = 'kimonix/sync_settings/orders_sync_limit';
    public const CONFIGPATH_KIMONIX_PRODUCTS_SYNC_LIMIT = 'kimonix/sync_settings/products_sync_limit';
    public const CONFIGPATH_KIMONIX_STORE_ID = 'kimonix/store/kimonix_store_id';
    public const CONFIGPATH_KIMONIX_ALLOW_DATA_SENDING = 'kimonix/store/allow_data_sending';
    public const CONFIGPATH_KIMONIX_DATA_PERIOD_DAYS = 'kimonix/store/data_period_days';
    public const CONFIGPATH_KIMONIX_IS_SETUP_FINISHED = 'kimonix/store/setup_finished';

    /**
     * Scope config object.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceConfig
     */
    private $resourceConfig;

    /**
     * Store manager object.
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var DateTimeFactory
     */
    private $datetimeFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var KimonixLogger
     */
    private $kimonixLogger;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @method __construct
     * @param  ScopeConfigInterface  $scopeConfig
     * @param  ResourceConfig        $resourceConfig
     * @param  StoreManagerInterface $storeManager
     * @param  EncryptorInterface    $encryptor
     * @param  DateTimeFactory          $datetimeFactory
     * @param  LoggerInterface       $logger
     * @param  KimonixLogger         $kimonixLogger
     * @param  UrlInterface          $urlBuilder
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceConfig $resourceConfig,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        DateTimeFactory $datetimeFactory,
        LoggerInterface $logger,
        KimonixLogger $kimonixLogger,
        UrlInterface $urlBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->datetimeFactory = $datetimeFactory;
        $this->logger = $logger;
        $this->kimonixLogger = $kimonixLogger;
        $this->urlBuilder = $urlBuilder;
    }

    public function getStoreManager()
    {
        return $this->storeManager;
    }

    public function getUrlBuilder()
    {
        return $this->urlBuilder;
    }

    /**
     * Return store id.
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Return config field value.
     *
     * @param string $fieldKey Field key.
     * @param string $scope Scope.
     * @param int    $scopeId Scope ID.
     *
     * @return mixed
     */
    private function getConfigValue($fieldKey, $scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        if (!$scope && $this->storeManager->isSingleStoreMode()) {
            return $this->scopeConfig->getValue($fieldKey);
        }
        return $this->scopeConfig->getValue(
            $fieldKey,
            $scope ?: ScopeInterface::SCOPE_STORE,
            is_null($scopeId) ? $this->getCurrentStoreId() : $scopeId
        );
    }

    /**
     * @method resetCredentials
     * @param  string                $scope Scope
     * @param  int|null              $storeId
     */
    public function resetCredentials($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        $this->resourceConfig->deleteConfig(self::CONFIGPATH_KIMONIX_ENABLED, $scope, $scopeId);
        $this->resourceConfig->deleteConfig(self::CONFIGPATH_KIMONIX_API_KEY, $scope, $scopeId);
        $this->resetStoreConfig($scope, $scopeId);
        return $this;
    }

    /**
     * @method resetStoreConfig
     * @param  string                $scope Scope
     * @param  int|null              $storeId
     */
    public function resetStoreConfig($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        $this->resourceConfig->deleteConfig(self::CONFIGPATH_KIMONIX_STORE_ID, $scope, $scopeId);
        $this->resourceConfig->deleteConfig(self::CONFIGPATH_KIMONIX_ALLOW_DATA_SENDING, $scope, $scopeId);
        $this->resourceConfig->deleteConfig(self::CONFIGPATH_KIMONIX_DATA_PERIOD_DAYS, $scope, $scopeId);
        $this->resourceConfig->deleteConfig(self::CONFIGPATH_KIMONIX_IS_SETUP_FINISHED, $scope, $scopeId);
        return $this;
    }

    /**
     * Is module enabled?
     * @param string $scope Scope.
     * @param int    $storeId Store ID.
     * @return bool
     */
    public function isEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return (bool)$this->getConfigValue(self::CONFIGPATH_KIMONIX_ENABLED, $scope, $scopeId);
    }

    /**
     * Is debug mode enabled?
     * @param string   $scope Scope.
     * @param int      $storeId Store ID.
     * @return bool
     */
    public function isDebugEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return (bool)$this->getConfigValue(self::CONFIGPATH_KIMONIX_DEBUG, $scope, $scopeId);
    }

    /**
     * Return Kimonix API key.
     * @param string   $scope Scope.
     * @param int      $storeId Store ID.
     * @return string
     */
    public function getKimonixApiKey($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return (($val = $this->getConfigValue(self::CONFIGPATH_KIMONIX_API_KEY, $scope, $scopeId))) ? $this->encryptor->decrypt($val) : null;
    }

    /**
     * @return int
     */
    public function getOrdersSyncLimit()
    {
        return (($limit = (int)$this->getConfigValue(self::CONFIGPATH_KIMONIX_ORDERS_SYNC_LIMIT)) > 0) ? $limit : 0;
    }

    /**
     * @return int
     */
    public function getProductsSyncLimit()
    {
        return (($limit = (int)$this->getConfigValue(self::CONFIGPATH_KIMONIX_PRODUCTS_SYNC_LIMIT)) > 0) ? $limit : 0;
    }

    /**
     * Return Kimonix store ID.
     * @param string   $scope Scope.
     * @param int      $storeId Store ID.
     * @return string
     */
    public function getKimonixStoreId($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return (string) $this->getConfigValue(self::CONFIGPATH_KIMONIX_STORE_ID, $scope, $scopeId);
    }

    /**
     * @method updateKimonixStoreId
     * @param  mixed                 $value
     * @param  string                $scope Scope
     * @param  int|null              $scopeId Scope ID.
     */
    public function updateKimonixStoreId($value = "", $scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        $this->resourceConfig->saveConfig(self::CONFIGPATH_KIMONIX_STORE_ID, $value, $scope, $scopeId);
        return $this;
    }

    /**
     * Return Kimonix allow_data_sending.
     * @param string   $scope Scope.
     * @param int      $scopeId Scope ID.
     * @return bool
     */
    public function getAllowDataSending($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return (bool) $this->getConfigValue(self::CONFIGPATH_KIMONIX_ALLOW_DATA_SENDING, $scope, $scopeId);
    }

    /**
     * @method updateAllowDataSending
     * @param  mixed                 $value
     * @param  string                $scope Scope
     * @param  int|null              $scopeId Scope ID.
     */
    public function updateAllowDataSending($value = "", $scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        $this->resourceConfig->saveConfig(self::CONFIGPATH_KIMONIX_ALLOW_DATA_SENDING, $value, $scope, $scopeId);
        return $this;
    }

    /**
     * Return Kimonix data_period_days.
     * @param string   $scope Scope.
     * @param int      $scopeId Scope ID.
     * @return string
     */
    public function getDataPeriodDays($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return (string) $this->getConfigValue(self::CONFIGPATH_KIMONIX_DATA_PERIOD_DAYS, $scope, $scopeId);
    }

    /**
     * @method updateDataPeriodDays
     * @param  mixed                 $value
     * @param  string                $scope Scope
     * @param  int|null              $scopeId Scope ID.
     */
    public function updateDataPeriodDays($value = "", $scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        $this->resourceConfig->saveConfig(self::CONFIGPATH_KIMONIX_DATA_PERIOD_DAYS, $value, $scope, $scopeId);
        return $this;
    }
    /**
     * Check if Kimonix setup is finished (not in progress)
     * @param string   $scope Scope.
     * @param int      $scopeId Scope ID.
     * @return bool
     */
    public function isSetupFinished($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return (bool) $this->getConfigValue(self::CONFIGPATH_KIMONIX_IS_SETUP_FINISHED, $scope, $scopeId);
    }

    /**
     * Set if Kimonix setup is finished (not in progress)
     * @method updateIsSetupFinished
     * @param  bool|null             $value
     * @param  string                $scope Scope
     * @param  int|null              $scopeId Scope ID.
     */
    public function updateIsSetupFinished($value, $scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        $this->resourceConfig->saveConfig(self::CONFIGPATH_KIMONIX_IS_SETUP_FINISHED, $value, $scope, $scopeId);
        return $this;
    }

    public function getUseSecureInFrontend($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return ($this->getConfigValue(Store::XML_PATH_SECURE_IN_FRONTEND, $scope, $scopeId)) ? true : false;
    }

    public function getSecureBaseUrl($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return (string) $this->getConfigValue(Store::XML_PATH_SECURE_BASE_URL, $scope, $scopeId);
    }

    public function getUnsecureBaseUrl($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return (string) $this->getConfigValue(Store::XML_PATH_UNSECURE_BASE_URL, $scope, $scopeId);
    }

    public function getBaseUrl($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return ($this->getUseSecureInFrontend($scope, $scopeId)) ? $this->getSecureBaseUrl($scope, $scopeId) : $this->getUnsecureBaseUrl($scope, $scopeId);
    }

    public function getCategoryUrlSuffix($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return $this->getConfigValue(CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX, $scope, $scopeId);
    }

    public function getProductUrlSuffix($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return $this->getConfigValue(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX, $scope, $scopeId);
    }

    public function isActiveReviews($scope = ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return $this->getConfigValue(ReviewConfig::XML_PATH_REVIEW_ACTIVE, $scope, $scopeId);
    }

    /**
     * @method getCurrentDate
     * @return date
     */
    public function getCurrentDate()
    {
        return $this->datetimeFactory->create()->gmtDate();
    }

    /**
     * @method getMainTableAlias
     * @param  \Magento\Framework\Data\Collection   $collection
     * @return string
     */
    public function getMainTableAlias(\Magento\Framework\Data\Collection $collection)
    {
        $mainTable = (string) $collection->getMainTable();
        foreach ((array) $collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM) as $alias => $tableInfo) {
            if (isset($tableInfo["tableName"]) && $tableInfo["tableName"] === $mainTable && $alias) {
                return (string) $alias;
            }
        }
        return "main_table";
    }

    /**
     * @method formatDate
     * @param  string     $date
     * @param  string     $format
     * @return string
     */
    public function formatDate($date, $format = self::KIMONIX_DATE_FORMAT)
    {
        return \date($format, strtotime($date));
    }

    /**
     * @method getCurrentStore
     */
    public function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @method getCurrentStoreId
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @method getCurrentStoreCurrencyCode
     * @return int
     */
    public function getCurrentStoreCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @method getRootCategoryId
     * @return int
     */
    public function getRootCategoryId()
    {
        foreach ($this->storeManager->getStores() as $store) {
            return $store->getRootCategoryId();
            break;
        }
    }

    /**
     * @method getDefaultStoreId
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * @method getKimonixApiUrl
     * @param string $path
     * @return string
     */
    public function getKimonixApiUrl($path = "")
    {
        return ((string) $this->getConfigValue(self::CONFIGPATH_KIMONIX_API_URL) ?: self::KIMONIX_API_URL)
            . (($path) ? '/' . $path : '');
    }

    /**
     * Set Kimonix API URL
     * @method updateKimonixApiUrl
     * @param  bool|null             $value
     */
    public function updateKimonixApiUrl($value)
    {
        $this->resourceConfig->saveConfig(self::CONFIGPATH_KIMONIX_API_URL, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $this->getDefaultStoreId());
        return $this;
    }

    /**
     * Log to var/log/kimonix_kimonix.log
     * @method log
     * @param  mixed  $message
     * @param  string $type
     * @param  array  $data
     * @return $this
     */
    public function log($message, $type = "debug", $data = [], $prefix = '[Kimonix] ')
    {
        if ($type !== 'debug' || $this->isDebugEnabled()) {
            if (!isset($data['magento_store_id'])) {
                $data['magento_store_id'] = $this->getCurrentStoreId();
            }
            if (!isset($data['kimonix_store_id'])) {
                $data['kimonix_store_id'] = $this->getKimonixStoreId();
            }
            if ($type === 'error') {
                $this->logger->error($prefix . json_encode($message), $data);
            }
            $this->kimonixLogger->info($prefix . json_encode($message), $data);
        }
        return $this;
    }
}
