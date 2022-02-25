<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Observer;

use Magento\Framework\Event\ObserverInterface;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Model\Schema as KimonixSchema;
use Magento\Framework\Registry;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ResourceConnection;
use Magento\Review\Model\ReviewFactory;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var KimonixConfig
     */
    private $kimonixConfig;

    /**
     * @var KimonixSchema
     */
    private $kimonixSchema;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @method __construct
     * @param  KimonixConfig      $kimonixConfig
     * @param  KimonixSchema      $kimonixSchema
     * @param  Registry           $registry
     * @param  ReviewFactory      $reviewFactory
     * @param  ResourceConnection $resourceConnection
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        KimonixSchema $kimonixSchema,
        Registry $registry,
        ReviewFactory $reviewFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->kimonixConfig = $kimonixConfig;
        $this->kimonixSchema = $kimonixSchema;
        $this->registry = $registry;
        $this->reviewFactory = $reviewFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Observer $observer)
    {
        if ($this->kimonixConfig->isEnabled()) {
            try {
                $product = $observer->getEvent()->getProduct();
                $productId = $product->getId();

                if (($origProductDataHash = $this->registry->registry('kimonix/product/watch_hash/id' . $productId))) {
                    $ratingSummary = $this->kimonixConfig->isActiveReviews() && ($entitySummary = $this->reviewFactory->create()->getEntitySummary($product)) ?
                        $entitySummary->getRatingSummary() : false;
                    $newProductDataHash = hash('sha256', json_encode([
                        $this->kimonixSchema->getProductSchema($product),
                        $ratingSummary ? $ratingSummary->getData() : null,
                    ]));

                    if ($origProductDataHash !== $newProductDataHash) {
                        $this->resourceConnection->getConnection('default')
                            ->insertOnDuplicate(
                                $this->resourceConnection->getTableName('kimonix_sync', 'default'),
                                [[
                                    "store_id" => $this->kimonixConfig->getDefaultStoreId(),
                                    "entity_type" => "products",
                                    "entity_id" => $productId,
                                    "sync_flag" => 0,
                                    "sync_date" => $this->kimonixConfig->getCurrentDate(),
                                ]],
                                ['store_id', 'entity_type', 'entity_id', 'sync_flag', 'sync_date']
                            );
                    }

                    $this->registry->unregister('kimonix/product/watch_hash/id' . $productId);
                }

                if ($this->registry->registry('kimonix/product/before')) {
                    $this->registry->unregister('kimonix/product/before');
                }

            } catch (\Exception $e) {
                $this->kimonixConfig->log("[Kimonix - ProductSaveAfter - ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
            }
        }
    }
}
