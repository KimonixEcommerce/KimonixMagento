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

class ProductAddToCartAfter implements ObserverInterface
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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @method __construct
     * @param  KimonixConfig      $kimonixConfig
     * @param  KimonixSchema      $kimonixSchema
     * @param  Registry           $registry
     * @param  ResourceConnection $resourceConnection
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        KimonixSchema $kimonixSchema,
        Registry $registry,
        ResourceConnection $resourceConnection
    ) {
        $this->kimonixConfig = $kimonixConfig;
        $this->kimonixSchema = $kimonixSchema;
        $this->registry = $registry;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Observer $observer)
    {
        if ($this->kimonixConfig->isEnabled()) {
            try {
                $product = $observer->getEvent()->getProduct();

                $this->resourceConnection->getConnection('default')->update(
                    $this->resourceConnection->getTableName('kimonix_sync', 'default'),
                    ['sync_flag' => 0],
                    ['entity_id = ?' => $product->getId(), 'entity_type = ?' => "products"]
                );
            } catch (\Exception $e) {
                $this->kimonixConfig->log("[Kimonix - ProductAddToCartAfter - ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
            }
        }
    }
}
