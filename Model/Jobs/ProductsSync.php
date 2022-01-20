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
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Kimonix\Kimonix\Model\AbstractJobs;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Model\Request\Factory as KimonixRequestFactory;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Reports\Model\ReportStatus;
use Magento\Reports\Model\Event as ReportsEvent;
use Magento\Review\Model\Review as ReviewModel;

class ProductsSync extends AbstractJobs
{
    /**
     * Event types / Request methods
     * @var array
     */
    const EVENT_TYPES_REQUEST_METHODS = [
        'created' => KimonixRequestFactory::PRODUCTS_UPSERT_REQUEST_METHOD,
        'updated' => KimonixRequestFactory::PRODUCTS_UPSERT_REQUEST_METHOD
    ];

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
     * @var ReportStatus
     */
    private $reportStatus;

    /**
     * @var ReviewModel
     */
    private $reviewModel;

    /**
     * @method __construct
     * @param  NotifierInterface        $notifierPool
     * @param  KimonixConfig            $kimonixConfig
     * @param  KimonixRequestFactory    $kimonixRequestFactory
     * @param  ResourceConnection       $resourceConnection
     * @param  AppEmulation             $appEmulation
     * @param  ProductCollectionFactory $productCollectionFactory
     * @param  ProductStatus            $productStatus
     * @param  ProductVisibility        $productVisibility
     * @param  ReportStatus             $reportStatus
     * @param  ReviewModel            $reviewModel
     */
    public function __construct(
        NotifierInterface $notifierPool,
        KimonixConfig $kimonixConfig,
        KimonixRequestFactory $kimonixRequestFactory,
        ResourceConnection $resourceConnection,
        AppEmulation $appEmulation,
        ProductCollectionFactory $productCollectionFactory,
        ProductStatus $productStatus,
        ProductVisibility $productVisibility,
        ReportStatus $reportStatus,
        ReviewModel $reviewModel
    ) {
        parent::__construct($notifierPool, $kimonixConfig, $kimonixRequestFactory, $resourceConnection, $appEmulation);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->reportStatus = $reportStatus;
        $this->reviewModel = $reviewModel;
    }

    /**
     * @method getSyncLimit
     * @return null|int
     */
    protected function getSyncLimit()
    {
        return $this->_kimonixConfig->getProductsSyncLimit();
    }

    /**
     * @method getProductCollection
     * @return ProductCollection
     * @api
     */
    protected function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $mainTable = $this->getMainTableAlias($collection);
        $idFieldName = $collection->getIdFieldName();

        $collection->addAttributeToSelect(['*']);

        $collection->getSelect()->joinLeft(
            ['kimonix_sync'=>$collection->getTable('kimonix_sync')],
            "{$mainTable}.{$idFieldName} = kimonix_sync.entity_id AND kimonix_sync.entity_type = 'products'",
            [
                'kimonix_sync_flag'=>'kimonix_sync.sync_flag'
            ]
        );

        $collection->setVisibility($this->productVisibility->getVisibleInCatalogIds());

        if ($this->reportStatus->isReportEnabled(ReportsEvent::EVENT_PRODUCT_TO_CART) &&
            ($dataPeriodDays = (int)$this->_kimonixConfig->getDataPeriodDays())
        ) {
            $fromDate = date('Y-m-d h:i:s', strtotime("-{$dataPeriodDays} day", strtotime($this->_kimonixConfig->getCurrentDate())));
            $collection->getSelect()->joinLeft(
                ['report_event'=>$collection->getTable('report_event')],
                "report_event.object_id = {$mainTable}.{$idFieldName} AND report_event.event_type_id = '". ReportsEvent::EVENT_PRODUCT_TO_CART ."' AND report_event.logged_at >= '{$fromDate}'",
                [
                    'adds_to_cart'=>'COUNT(DISTINCT report_event.event_id)'
                ]
            );
        }

        if ($this->_kimonixConfig->isActiveReviews()) {
            $collection->getSelect()->join(
                ['review_entity_summary'=>$collection->getTable('review_entity_summary')],
                "review_entity_summary.entity_pk_value = {$mainTable}.{$idFieldName} AND review_entity_summary.entity_type = '". $this->reviewModel->getEntityIdByCode(ReviewModel::ENTITY_PRODUCT_CODE) ."'",
                [
                    'num_reviews'=>'SUM(DISTINCT review_entity_summary.reviews_count)',
                    'avg_rating'=>'ROUND(AVG(DISTINCT review_entity_summary.rating_summary))'
                ]
            );
        }

        $collection->getSelect()->group("{$this->getMainTableAlias($collection)}.{$collection->getIdFieldName()}");

        return $collection;
    }

    /**
     * @method addProductCollectionFilters
     * @param  ProductCollection           $collection
     * @param  string                    $eventType (created/updated)
     * @return ProductCollection
     * @api
     */
    protected function addProductCollectionFilters(ProductCollection $collection, $eventType)
    {
        switch ($eventType) {
            case 'created':
                //$collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
                $collection->getSelect()->where("kimonix_sync.sync_flag IS NULL");
                $collection->getSelect()->order([
                    "{$this->getMainTableAlias($collection)}.created_at ASC",
                ]);
                break;
            case 'updated':
                $collection->getSelect()->where("kimonix_sync.sync_flag = '0'");
                $collection->addAttributeToSort("{$this->getMainTableAlias($collection)}.updated_at", 'ASC');
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

    public function execute()
    {
        try {
            $this->emulateAdminArea();
            if (!$this->_kimonixConfig->isEnabled()) {
                $this->_processOutput("ProductsSync::execute() - Kimonix is disabled [SKIPPING]", "debug");
                return $this;
            }
            if (!$this->_kimonixConfig->getAllowDataSending()) {
                $this->_processOutput("ProductsSync::execute() - Kimonix data sending is not allowed for this store. [SKIPPING]", "error");
                return $this;
            }

            foreach (self::EVENT_TYPES_REQUEST_METHODS as $eventType => $requestMethod) {
                $this->_processOutput("ProductsSync::execute() - Processing `{$eventType}` products ...", "debug");
                if ($this->getLimit() && ($this->getLimit() - $this->limitSubstract) < 1) {
                    $this->_processOutput("ProductsSync::execute() - Products sync limit exceeded. [SKIPPING]", "debug");
                    return $this;
                }

                $productsCollection = $this->getProductCollection();
                $productsCollection = $this->addProductCollectionFilters($productsCollection, $eventType);
                $productsCollection = $this->setCollectionLimit($productsCollection);

                $productsCollectionCount = $productsCollection->count();
                $this->_processOutput("ProductsSync::execute() - Found {$productsCollectionCount} products for sync...", "debug");
                if (!$productsCollectionCount) {
                    continue;
                }

                $request = $this->getRequestFactory()
                    ->create($requestMethod)
                    ->setProductsCollection($productsCollection)
                    ->prepare();

                if ($request->canExecute()) {
                    $response = $request->execute($this->getOutput());
                }

                $this->_processOutput("ProductsSync::execute() --- Sent {$request->getPreparedProductsCount()} `{$eventType}` products.", "debug");
                $this->_processOutput("ProductsSync::execute() --- Skipped {$request->getSkippedProductsCount()} `{$eventType}` products.", "debug");
                $this->_processOutput("ProductsSync::execute() --- Flagging `{$eventType}` products...", "debug");
                $this->flagItems('products', $this->getCollectionIds($productsCollection));
                $this->_processOutput("ProductsSync::execute() --- Processing `{$eventType}` products [SUCCESS]", "debug");
                $this->limitSubstract += $request->getPreparedProductsCount();
            }

            $this->updateSetupProgressIfNeeded();

        } catch (\Exception $e) {
            $this->_processOutput("ProductsSync::execute() - Exception: Failed to sync products. " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
        }

        $this->stopEnvironmentEmulation();
        return $this;
    }
}
