<?php
/**
 * Kimonix Module For Magento 2
 *
 * @order Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Request\Orders;

use Kimonix\Kimonix\Model\AbstractRequest;
use Kimonix\Kimonix\Model\Request\Factory as RequestFactory;
use Kimonix\Kimonix\Model\Response\Factory as ResponseFactory;
use Magento\Sales\Model\Order;

/**
 * Kimonix orders/delete request model.
 */
class Delete extends AbstractRequest
{
    /**
     * @var Order
     */
    protected $_order;

    /**
     * @param  Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * @return Order $order
     */
    public function getOrder()
    {
        return $this->_order;
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
        return RequestFactory::ORDERS_DELETE_REQUEST_METHOD;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getResponseHandlerType()
    {
        return ResponseFactory::ORDERS_DELETE_RESPONSE_HANDLER;
    }

    /**
     * Return request params.
     *
     * @return array
     */
    protected function getParams()
    {
        return array_replace_recursive(
            parent::getParams(),
            [
              'id' => $this->getOrder()->getId()
            ]
        );
    }
}
