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

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Kimonix\Kimonix\Model\AbstractJobs;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Model\Request\Factory as KimonixRequestFactory;
use Symfony\Component\Console\Output\OutputInterface;

class OrdersSync extends AbstractJobs
{
    /**
     * Event types / Request methods
     * @var array
     */
    const EVENT_TYPES_REQUEST_METHODS = [
        'created' => KimonixRequestFactory::ORDERS_UPSERT_REQUEST_METHOD,
        'updated' => KimonixRequestFactory::ORDERS_UPSERT_REQUEST_METHOD
    ];

    /**
     * @var string
     */
    protected $_resourceConnectionType = 'sales';

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @method __construct
     * @param  NotifierInterface      $notifierPool
     * @param  KimonixConfig          $kimonixConfig
     * @param  KimonixRequestFactory  $kimonixRequestFactory
     * @param  ResourceConnection     $resourceConnection
     * @param  AppEmulation           $appEmulation
     * @param  OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        NotifierInterface $notifierPool,
        KimonixConfig $kimonixConfig,
        KimonixRequestFactory $kimonixRequestFactory,
        ResourceConnection $resourceConnection,
        AppEmulation $appEmulation,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        parent::__construct($notifierPool, $kimonixConfig, $kimonixRequestFactory, $resourceConnection, $appEmulation);
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @method getOrderCollection
     * @return OrderCollection
     * @api
     */
    protected function getOrderCollection()
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['kimonix_sync_sales'=>$collection->getTable('kimonix_sync_sales')],
            "{$this->getMainTableAlias($collection)}.{$collection->getIdFieldName()} = kimonix_sync_sales.entity_id AND kimonix_sync_sales.entity_type = 'orders'",
            [
                'kimonix_sync_flag'=>'MAX(kimonix_sync_sales.sync_flag)'
            ]
        );

        return $collection;
    }

    /**
     * @method addOrderCollectionFilters
     * @param  OrderCollection           $collection
     * @param  string                    $eventType (created/updated)
     * @return OrderCollection
     * @api
     */
    protected function addOrderCollectionFilters(OrderCollection $collection, $eventType)
    {
        switch ($eventType) {
            case 'created':
                $collection->addAttributeToFilter('kimonix_sync_sales.sync_flag', ['null' => true]);
                $collection->getSelect()->order([
                    "{$this->getMainTableAlias($collection)}.created_at ASC",
                ]);
                break;
            case 'updated':
                $collection->addAttributeToFilter('kimonix_sync_sales.sync_flag', ['eq' => 0]);
                $collection->getSelect()->order([
                    "{$this->getMainTableAlias($collection)}.updated_at ASC",
                ]);
                break;

            default:
                throw new \Exception("eventType must be `created` or `updated`.", 1);
                break;
        }
        return $collection;
    }

    /**
     * @method getSyncLimit
     * @return null|int
     */
    protected function getSyncLimit()
    {
        return $this->_kimonixConfig->getOrdersSyncLimit();
    }

    public function execute()
    {
        try {
            $this->emulateAdminArea();
            if (!$this->_kimonixConfig->isEnabled()) {
                $this->_processOutput("OrdersSync::execute() - Kimonix is disabled [SKIPPING]", "debug");
                return $this;
            }
            if (!$this->_kimonixConfig->getAllowDataSending()) {
                $this->_processOutput("OrdersSync::execute() - Kimonix data sending is not allowed for this store. [SKIPPING]", "error");
                return $this;
            }

            foreach (self::EVENT_TYPES_REQUEST_METHODS as $eventType => $requestMethod) {
                $this->_processOutput("OrdersSync::execute() - Processing `{$eventType}` orders ...", "debug");
                if ($this->getLimit() && ($this->getLimit() - $this->limitSubstract) < 1) {
                    $this->_processOutput("ProductsSync::execute() - Orders sync limit exceeded. [SKIPPING]", "debug");
                    return $this;
                }

                $ordersCollection = $this->getOrderCollection();
                $ordersCollection = $this->addOrderCollectionFilters($ordersCollection, $eventType);
                $ordersCollection = $this->setCollectionLimit($ordersCollection);

                $ordersCollectionCount = $ordersCollection->count();
                $this->_processOutput("OrdersSync::execute() - Found {$ordersCollectionCount} orders for sync...", "debug");
                if (!$ordersCollectionCount) {
                    continue;
                }

                $request = $this->getRequestFactory()
                    ->create($requestMethod)
                    ->setOrdersCollection($ordersCollection)
                    ->prepare();

                if ($request->canExecute()) {
                    $response = $request->execute($this->getOutput());
                }

                $this->_processOutput("OrdersSync::execute() --- Sent {$request->getPreparedOrdersCount()} `{$eventType}` orders.", "debug");
                $this->_processOutput("OrdersSync::execute() --- Skipped {$request->getSkippedOrdersCount()} `{$eventType}` orders.", "debug");
                $this->_processOutput("OrdersSync::execute() --- Flagging `{$eventType}` orders...", "debug");
                $this->flagItems('orders', $this->getCollectionIds($ordersCollection));
                $this->_processOutput("OrdersSync::execute() --- Processing `{$eventType}` orders [SUCCESS]", "debug");
                $this->limitSubstract += $request->getPreparedOrdersCount();
            }

            $this->updateSetupProgressIfNeeded();

        } catch (\Exception $e) {
            $this->_processOutput("OrdersSync::execute() - Exception: Failed to sync orders. " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
        }

        $this->stopEnvironmentEmulation();
        return $this;
    }
}
