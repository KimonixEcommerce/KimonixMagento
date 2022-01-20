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
use Kimonix\Kimonix\Model\Request\Factory as KimonixRequestFactory;
use Kimonix\Kimonix\Model\SyncFactory as KimonixSyncFactory;
use Magento\Framework\Event\Observer;

class OrderDeleteBefore implements ObserverInterface
{
    /**
     * @var KimonixConfig
     */
    private $kimonixConfig;

    /**
     * @var KimonixRequestFactory
     */
    private $kimonixRequestFactory;

    /**
     * @var KimonixSyncFactory
     */
    private $kimonixSyncFactory;

    /**
     * @method __construct
     * @param  KimonixConfig         $kimonixConfig
     * @param  KimonixRequestFactory $kimonixRequestFactory
     * @param  KimonixSyncFactory    $kimonixSyncFactory
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        KimonixRequestFactory $kimonixRequestFactory,
        KimonixSyncFactory $kimonixSyncFactory
    ) {
        $this->kimonixConfig = $kimonixConfig;
        $this->kimonixRequestFactory = $kimonixRequestFactory;
        $this->kimonixSyncFactory = $kimonixSyncFactory;
    }

    public function execute(Observer $observer)
    {
        if ($this->kimonixConfig->isEnabled()) {
            try {
                $order = $observer->getEvent()->getOrder();
                $request = $this->kimonixRequestFactory
                    ->create(KimonixRequestFactory::ORDERS_DELETE_REQUEST_METHOD)
                    ->setOrder($order)
                    ->execute();

                $syncFlag = $this->kimonixSyncFactory->create()->getCollection()
                    ->addFieldToFilter('entity_type', 'orders')
                    ->addFieldToFilter('entity_id', $order->getId())
                    ->setPageSize(1)
                    ->getFirstItem();
                if ($syncFlag && $syncFlag->getId()) {
                    $syncFlag->delete();
                }
            } catch (\Exception $e) {
                $this->kimonixConfig->log("[Kimonix - OrderDeleteBefore - ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
                throw $e;
            }
        }
    }
}
