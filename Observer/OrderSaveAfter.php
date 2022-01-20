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

class OrderSaveAfter implements ObserverInterface
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
                $order = $observer->getEvent()->getOrder();
                $orderId = $order->getId();

                if (($origOrderDataHash = $this->registry->registry('kimonix/order/watch_hash/id' . $orderId))) {
                    $newOrderDataHash = hash('sha256', json_encode([
                        $this->kimonixSchema->getOrderSchema($order)
                    ]));

                    if ($origOrderDataHash !== $newOrderDataHash) {
                        $this->resourceConnection->getConnection('sales')
                            ->insertOnDuplicate(
                                $this->resourceConnection->getTableName('kimonix_sync_sales', 'sales'),
                                [[
                                    "store_id" => $this->kimonixConfig->getDefaultStoreId(),
                                    "entity_type" => "orders",
                                    "entity_id" => $orderId,
                                    "sync_flag" => 0,
                                    "sync_date" => $this->kimonixConfig->getCurrentDate(),
                                ]],
                                ['store_id', 'entity_type', 'entity_id', 'sync_flag', 'sync_date']
                            );
                    }

                    $this->registry->unregister('kimonix/order/watch_hash/id' . $orderId);
                }

                if ($this->registry->registry('kimonix/order/before')) {
                    $this->registry->unregister('kimonix/order/before');
                }

            } catch (\Exception $e) {
                $this->kimonixConfig->log("[Kimonix - OrderSaveAfter - ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
            }
        }
    }
}
