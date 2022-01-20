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
use Magento\Framework\Event\Observer;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Model\Schema as KimonixSchema;
use Magento\Framework\Registry;
use Magento\Review\Model\ReviewFactory;
use Magento\Catalog\Model\ProductFactory;

class ProductSaveBefore implements ObserverInterface
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
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @method __construct
     * @param  KimonixConfig  $kimonixConfig
     * @param  KimonixSchema  $kimonixSchema
     * @param  Registry       $registry
     * @param  ReviewFactory  $reviewFactory
     * @param  ProductFactory $productFactory
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        KimonixSchema $kimonixSchema,
        Registry $registry,
        ReviewFactory $reviewFactory,
        ProductFactory $productFactory
    ) {
        $this->kimonixConfig = $kimonixConfig;
        $this->kimonixSchema = $kimonixSchema;
        $this->registry = $registry;
        $this->reviewFactory = $reviewFactory;
        $this->productFactory = $productFactory;
    }

    public function execute(Observer $observer)
    {
        if ($this->kimonixConfig->isEnabled()) {
            try {
                if (!$this->registry->registry("kimonix/product/before")) {
                    $this->registry->register('kimonix/product/before', true);
                    $eventProduct = $observer->getEvent()->getProduct();
                    if (!$eventProduct->isObjectNew()) {
                        $productId = $eventProduct->getId();
                        $product = $this->productFactory->create()->load($productId);
                        $ratingSummary = $this->kimonixConfig->isActiveReviews() ? $this->reviewFactory->create()->getEntitySummary($product)->getRatingSummary() : false;
                        $this->registry->register(
                            'kimonix/product/watch_hash/id' . $productId,
                            hash('sha256', json_encode([
                                $this->kimonixSchema->getProductSchema($product),
                                $ratingSummary ? $ratingSummary->getData() : null,
                            ]))
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->kimonixConfig->log("[Kimonix - ProductSaveBefore - ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
            }
        }
    }
}
