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
use Magento\Sales\Model\OrderFactory;

class OrderSaveBefore implements ObserverInterface
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
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @method __construct
     * @param  KimonixConfig $kimonixConfig
     * @param  KimonixSchema $kimonixSchema
     * @param  Registry      $registry
     * @param  OrderFactory  $orderFactory
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        KimonixSchema $kimonixSchema,
        Registry $registry,
        OrderFactory $orderFactory
    ) {
        $this->kimonixConfig = $kimonixConfig;
        $this->kimonixSchema = $kimonixSchema;
        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->kimonixConfig->isEnabled()) {
            try {
                if (!$this->registry->registry("kimonix/order/before")) {
                    $this->registry->register('kimonix/order/before', true);
                    $eventOrder = $observer->getEvent()->getOrder();
                    if (!$eventOrder->isObjectNew()) {
                        $orderId = $eventOrder->getId();
                        $order = $this->orderFactory->create()->load($orderId);
                        $this->registry->register(
                            'kimonix/order/watch_hash/id' . $orderId,
                            hash('sha256', json_encode([
                                $this->kimonixSchema->getOrderSchema($order)
                            ]))
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->kimonixConfig->log("[Kimonix - OrderSaveBefore - ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
            }
        }
    }
}
