<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Request\Orders;

use Kimonix\Kimonix\Model\AbstractRequest;
use Kimonix\Kimonix\Model\Request\Factory as RequestFactory;
use Kimonix\Kimonix\Model\Response\Factory as ResponseFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Model\Schema as KimonixSchema;
use Kimonix\Kimonix\Lib\Http\Client\Curl;

/**
 * Kimonix orders/upsert request model.
 */
class Upsert extends AbstractRequest
{
    /**
     * @var null|array
     */
    private $preparedParams;

    /**
     * @var int
     */
    private $preparedOrdersCount = 0;

    /**
     * @var int
     */
    private $skippedOrdersCount = 0;

    /**
     * @var OrderCollection
     */
    private $ordersCollection;

    /**
     * @var KimonixSchema
     */
    private $kimonixSchema;

    /**
     * @method __construct
     * @param  KimonixConfig   $kimonixConfig
     * @param  Curl            $curl
     * @param  ResponseFactory $responseFactory
     * @param  KimonixSchema   $kimonixSchema
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        Curl $curl,
        ResponseFactory $responseFactory,
        KimonixSchema $kimonixSchema
    ) {
        parent::__construct($kimonixConfig, $curl, $responseFactory);
        $this->kimonixSchema = $kimonixSchema;
    }

    /**
     * @method setOrdersCollection
     * @param  OrderCollection     $ordersCollection
     * @return AbstractRequest
     */
    public function setOrdersCollection(OrderCollection $ordersCollection)
    {
        $this->ordersCollection = $ordersCollection;
        return $this;
    }

    /**
     * @method getOrdersCollection
     * @return OrderCollection
     */
    public function getOrdersCollection()
    {
        return $this->ordersCollection;
    }

    /**
     * Check if request can be executed
     * @return bool
     */
    public function canExecute()
    {
        return parent::canExecute() && (bool) $this->getPreparedOrdersCount();
    }

    /**
     * @return AbstractRequest
     */
    public function prepare()
    {
        $this->reset();
        $this->preparedParams = $this->getParams();
        return $this;
    }

    /**
     * @return AbstractRequest
     */
    public function reset()
    {
        $this->preparedParams = null;
        $this->preparedOrdersCount = 0;
        $this->skippedOrdersCount = 0;
        return $this;
    }


    /**
     * @return array
     */
    protected function getParams()
    {
        if ($this->preparedParams !== null) {
            return $this->preparedParams;
        }

        $this->preparedParams = [];

        foreach ($this->getOrdersCollection() as $order) {
            try {
                $this->_kimonixConfig->log("Request\Orders\Upsert::getParams() preparing order ID: {$order->getId()} ...", 'debug');
                $this->preparedParams[] = $this->kimonixSchema->getOrderSchema($order);
                $this->preparedOrdersCount++;
            } catch (\Exception $e) {
                $this->_kimonixConfig->log("Request\Orders\Upsert::getParams()- Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'debug');
                $this->skippedOrdersCount++;
            }
        }

        return $this->preparedParams;
    }

    /**
     * @return int
     */
    public function getPreparedOrdersCount()
    {
        return $this->preparedOrdersCount;
    }

    /**
     * @return int
     */
    public function getSkippedOrdersCount()
    {
        return $this->skippedOrdersCount;
    }

    /**
     * @return string
     */
    protected function getCurlMethod()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getRequestMethod()
    {
        return RequestFactory::ORDERS_UPSERT_REQUEST_METHOD;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getResponseHandlerType()
    {
        return ResponseFactory::ORDERS_UPSERT_RESPONSE_HANDLER;
    }
}
