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

use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Model\Request\Factory as KimonixRequestFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Symfony\Component\Console\Output\OutputInterface;
use \Magento\Framework\Data\Collection;
use \Magento\Framework\ObjectManagerInterface;

abstract class AbstractJobs
{
    /**
     * @var mixed
     */
    private $adminNotificationError = false;

    /**
     * Main Table Alias
     * @var string[]
     */
    private $mainTableAlias = [];

    /**
     * @var string
     */
    protected $_resourceConnectionType = 'default';

    /**
     * Products Sync Limit:
     */
    protected $limit = null;

    /**
     * Products Sync Limit Substract (reduce limit by this value):
     */
    protected $limitSubstract = 0;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var NotifierInterface
     */
    private $notifierPool;

    /**
     * @var AppEmulation
     */
    private $appEmulation;

    /**
     * @var KimonixConfig
     */
    protected $_kimonixConfig;

    /**
     * @var KimonixRequestFactory
     */
    protected $_kimonixRequestFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @method __construct
     * @param  NotifierInterface     $notifierPool
     * @param  AppEmulation          $appEmulation
     * @param  KimonixConfig         $kimonixConfig
     * @param  KimonixRequestFactory $kimonixRequestFactory
     * @param  ResourceConnection    $resourceConnection
     */
    public function __construct(
        NotifierInterface $notifierPool,
        KimonixConfig $kimonixConfig,
        KimonixRequestFactory $kimonixRequestFactory,
        ResourceConnection $resourceConnection,
        AppEmulation $appEmulation
    ) {
        $this->notifierPool = $notifierPool;
        $this->appEmulation = $appEmulation;
        $this->_kimonixConfig = $kimonixConfig;
        $this->_kimonixRequestFactory = $kimonixRequestFactory;
        $this->_resourceConnection = $resourceConnection;
    }

    /**
     * @return AbstractJobs
     */
    abstract public function execute();

    /**
     * @method getObjectManager
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * @method strToCamelCase
     * @param  string         $str
     * @param  string         $prefix
     * @param  string         $suffix
     * @return string
     */
    public function strToCamelCase($str, $prefix = '', $suffix = '')
    {
        return $prefix . str_replace('_', '', ucwords($str, '_')) . $suffix;
    }

    /**
     * @method initConfig
     * @param array $config
     * @return AbstractJobs
     */
    public function initConfig(array $config)
    {
        foreach ($config as $key => $val) {
            $method = $this->strToCamelCase(strtolower($key), 'set');
            if (method_exists($this, $method)) {
                $this->{$method}($val);
            }
        }
        return $this;
    }

    /**
     * @method setOutput
     * @param OutputInterface|null $output
     */
    public function setOutput(OutputInterface $output = null)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @method getOutput
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @method getRequestFactory
     * @return RequestInterface
     */
    protected function getRequestFactory()
    {
        return $this->_kimonixRequestFactory;
    }

    /**
     * Process output messages (log to kimonix_kimonix.log / output to terminal)
     * @method _processOutput
     * @return AbstractJobs
     */
    protected function _processOutput($message, $type = "info", $data = [])
    {
        if ($this->output instanceof OutputInterface) {
            //Output to terminal
            $outputType = ($type === "error") ? $type : "info";
            $this->output->writeln('<' . $outputType . '>' . json_encode($message) . '</' . $outputType . '>');
            if ($data) {
                $this->output->writeln('<comment>' . json_encode($data) . '</comment>');
            }
        } else {
            //Add admin error notification
            if ($type === 'error' && !$this->adminNotificationError) {
                $this->addAdminNotification("Kimonix - An error occurred during the automated sync process! (module: Kimonix_Kimonix)", "*If you enabled debug mode on Kimonix, you should see more details in the log file (var/log/kimonix_kimonix.log)", 'critical');
                $this->adminNotificationError = true;
            }
        }

        $this->_kimonixConfig->log($message, $type, $data);

        return $this;
    }

    private function addAdminNotification(string $title, $description = "", $type = 'critical')
    {
        $method = 'add' . ucfirst($type);
        $this->notifierPool->{$method}($title, $description);
        return $this;
    }

    /**
     * Stop environment emulation
     * @return AbstractJobs
     */
    protected function stopEnvironmentEmulation()
    {
        $this->appEmulation->stopEnvironmentEmulation();
        return $this;
    }

    /**
     * Start admin environment emulation
     * @return AbstractJobs
     */
    protected function emulateAdminArea()
    {
        $this->appEmulation->startEnvironmentEmulation(
            \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            Area::AREA_FRONTEND,
            true
        );
        return $this;
    }

    /**
     * @method getMainTableAlias
     * @param  Collection   $collection
     * @return string
     */
    protected function getMainTableAlias(Collection $collection)
    {
        $className = get_class($collection);
        if (empty($this->mainTableAlias[$className])) {
            $this->mainTableAlias[$className] = $this->_kimonixConfig->getMainTableAlias($collection);
        }
        return $this->mainTableAlias[$className];
    }

    protected function getKimonixSyncTableName()
    {
        if ($this->_resourceConnectionType === 'default') {
            return 'kimonix_sync';
        }
        return "kimonix_sync_{$this->_resourceConnectionType}";
    }

    protected function flagItems($entityType, array $entityIds, $flag = 1, $storeId = null)
    {
        foreach ($entityIds as &$entityId) {
            $entityId = [
                "store_id" => $storeId ?: $this->_kimonixConfig->getDefaultStoreId(),
                "entity_type" => $entityType,
                "entity_id" => $entityId,
                "sync_flag" => $flag ? 1 : 0,
                "sync_date" => $this->_kimonixConfig->getCurrentDate(),
            ];
        }
        return $this->_resourceConnection->getConnection($this->_resourceConnectionType)
            ->insertOnDuplicate($this->_resourceConnection->getTableName($this->getKimonixSyncTableName(), $this->_resourceConnectionType), $entityIds, ['store_id', 'entity_type', 'entity_id', 'sync_flag', 'sync_date']);
    }

    /**
     * @method setCollectionLimit
     * @param  Collection         $collection
     * @param  int|null           $limit
     * @return Collection
     * @api
     */
    protected function setCollectionLimit(Collection $collection, $limit = null)
    {
        if (is_null($limit)) {
            $limit = $this->getLimit();
        }
        if ($limit) {
            $limit -= $this->limitSubstract;
            if ($limit > 0) {
                $collection->getSelect()->limit($limit);
            }
        } /*else{
            $collection->getSelect()->limit($limit);
        }*/
        return $collection;
    }

    /**
     * @method setLimit
     * @param null|int $limit
     * @return AbstractJobs
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @method getLimit
     * @return null|int
     */
    public function getLimit()
    {
        return $this->limit === null ? $this->getSyncLimit() : $this->limit;
    }

    /**
     * @method getInitialLimit
     * @return null|int
     */
    protected function getSyncLimit()
    {
        return null;
    }

    /**
     * @method getCollectionIds
     * @param  Collection $collection
     * @return array
     */
    protected function getCollectionIds(Collection $collection)
    {
        $ids = [];
        foreach ($collection as $item) {
            $ids[] = $item->getId();
        }
        return $ids;
    }

    /**
     * @method updateSetupProgressIfNeeded
     * @return AbstractJobs
     */
    protected function updateSetupProgressIfNeeded()
    {
        if (!$this->_kimonixConfig->isSetupFinished()) {
            $job = $this->getObjectManager()->get(\Kimonix\Kimonix\Model\Jobs\SetupProgressUpdate::class);
            if (($output = $this->getOutput())) {
                $job->setOutput($output);
            }
            $job->execute();
        }
        return $this;
    }
}
