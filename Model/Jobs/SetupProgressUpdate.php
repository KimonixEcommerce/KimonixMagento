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
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Kimonix\Kimonix\Model\AbstractJobs;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Model\Request\Factory as KimonixRequestFactory;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;

class SetupProgressUpdate extends AbstractJobs
{
    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductStatus
     */
    private $productStatus;

    /**
     * @var ProductVisibility
     */
    private $productVisibility;

    /**
     * @method __construct
     * @param  NotifierInterface        $notifierPool
     * @param  KimonixConfig            $kimonixConfig
     * @param  KimonixRequestFactory    $kimonixRequestFactory
     * @param  ResourceConnection       $resourceConnection
     * @param  AppEmulation             $appEmulation
     * @param  OrderCollectionFactory   $orderCollectionFactory
     * @param  ProductCollectionFactory $productCollectionFactory
     * @param  ProductStatus            $productStatus
     * @param  ProductVisibility        $productVisibility
     */
    public function __construct(
        NotifierInterface $notifierPool,
        KimonixConfig $kimonixConfig,
        KimonixRequestFactory $kimonixRequestFactory,
        ResourceConnection $resourceConnection,
        AppEmulation $appEmulation,
        OrderCollectionFactory $orderCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductStatus $productStatus,
        ProductVisibility $productVisibility
    ) {
        parent::__construct($notifierPool, $kimonixConfig, $kimonixRequestFactory, $resourceConnection, $appEmulation);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
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
                'kimonix_sync_flag'=>'kimonix_sync_sales.sync_flag'
            ]
        );

        return $collection;
    }

    /**
     * @method addOrderCollectionFilters
     * @param  OrderCollection           $collection
     * @return OrderCollection
     * @api
     */
    protected function addOrderCollectionFilters(OrderCollection $collection)
    {
        $collection->addAttributeToFilter('kimonix_sync_sales.sync_flag', ['notnull' => true]);
        return $collection;
    }

    /**
     * @method getProductCollection
     * @return ProductCollection
     * @api
     */
    protected function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['kimonix_sync'=>$collection->getTable('kimonix_sync')],
            "{$this->getMainTableAlias($collection)}.{$collection->getIdFieldName()} = kimonix_sync.entity_id AND kimonix_sync.entity_type = 'products'",
            [
                'kimonix_sync_flag'=>'MAX(kimonix_sync.sync_flag)'
            ]
        );
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->productVisibility->getVisibleInCatalogIds());
        $collection->getSelect()->group("{$this->getMainTableAlias($collection)}.{$collection->getIdFieldName()}");

        return $collection;
    }

    /**
     * @method addProductCollectionFilters
     * @param  ProductCollection           $collection
     * @return ProductCollection
     * @api
     */
    protected function addProductCollectionFilters(ProductCollection $collection)
    {
        $collection->getSelect()->where("kimonix_sync.sync_flag IS NOT NULL");
        return $collection;
    }

    public function execute()
    {
        try {
            $this->emulateAdminArea();
            if (!$this->_kimonixConfig->isEnabled()) {
                $this->_processOutput("SetupProgressUpdate::execute() - Kimonix is disabled [SKIPPING]", "debug");
                return $this;
            }
            if (!$this->_kimonixConfig->getAllowDataSending()) {
                $this->_processOutput("SetupProgressUpdate::execute() - Kimonix data sending is not allowed for this store. [SKIPPING]", "error");
                return $this;
            }
            if ($this->_kimonixConfig->isSetupFinished()) {
                $this->_processOutput("SetupProgressUpdate::execute() - Kimonix setup already finished. [SKIPPING]", "error");
                return $this;
            }

            //Orders:
            $allOrdersCount = $this->getOrderCollection()
                ->getSize();
            $ordersCollection = $this->getOrderCollection();
            $ordersCollection = $this->addOrderCollectionFilters($ordersCollection);
            $syncedOrdersCount = $ordersCollection->getSize();
            $this->_processOutput("SetupProgressUpdate::execute() - Orders sync status: {$syncedOrdersCount}/{$allOrdersCount}", "debug");

            //Products:
            $allProductsCount = $this->getProductCollection()
                ->getSize();
            $productsCollection = $this->getProductCollection();
            $productsCollection = $this->addProductCollectionFilters($productsCollection);
            $syncedProductsCount = $productsCollection->getSize();
            $this->_processOutput("SetupProgressUpdate::execute() - Products sync status: {$syncedProductsCount}/{$allProductsCount}", "debug");

            //Totals:
            $totalRecords = $allOrdersCount + $allProductsCount;
            $totalSyncedRecords = $syncedOrdersCount + $syncedProductsCount;
            $decimalProgress = round($totalRecords > $totalSyncedRecords ? ($totalSyncedRecords / $totalRecords) : 1, 3);
            $percentageProgress = $decimalProgress * 100;
            $this->_processOutput("SetupProgressUpdate::execute() ------------------------", "debug");
            $this->_processOutput("SetupProgressUpdate::execute() - Total sync status: {$totalSyncedRecords}/{$totalRecords}", "debug");
            $this->_processOutput("SetupProgressUpdate::execute() - Sync progress: {$percentageProgress}% ({$decimalProgress})", "debug");

            $this->_processOutput("SetupProgressUpdate::execute() --- Setup still in progress. Updating Kimonix...", "debug");
            $request = $this->getRequestFactory()
                ->create(KimonixRequestFactory::SETUP_PROGRESS_REQUEST_METHOD)
                ->setProgress($decimalProgress)
                ->setStage("Initial sync in progress. {$totalSyncedRecords}/{$totalRecords} records sent ({$percentageProgress}%).")
                ->execute($this->getOutput());
            $this->_processOutput("SetupProgressUpdate::execute() --- [SUCCESS]", "debug");

            if ($decimalProgress >= 1) {
                $this->_processOutput("SetupProgressUpdate::execute() --- Setup is finished! Updating Kimonix...", "debug");
                $request = $this->getRequestFactory()
                    ->create(KimonixRequestFactory::SETUP_FINISHED_REQUEST_METHOD)
                    ->execute($this->getOutput())
                    ->update(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $this->_kimonixConfig->getDefaultStoreId());
                $this->_processOutput("SetupProgressUpdate::execute() --- [SUCCESS]", "debug");
            }
        } catch (\Exception $e) {
            $this->_processOutput("SetupProgressUpdate::execute() - Exception: Failed to sync products. " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
        }

        $this->stopEnvironmentEmulation();
        return $this;
    }
}
