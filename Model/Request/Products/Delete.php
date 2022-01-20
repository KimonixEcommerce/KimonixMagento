<?php
/**
 * Kimonix Module For Magento 2
 *
 * @product Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Request\Products;

use Kimonix\Kimonix\Model\AbstractRequest;
use Kimonix\Kimonix\Model\Request\Factory as RequestFactory;
use Kimonix\Kimonix\Model\Response\Factory as ResponseFactory;
use Magento\Catalog\Model\Product;

/**
 * Kimonix products/delete request model.
 */
class Delete extends AbstractRequest
{
    /**
     * @var Product
     */
    protected $_product;

    /**
     * @param  Product $product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * @return Product $product
     */
    public function getProduct()
    {
        return $this->_product;
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
        return RequestFactory::PRODUCTS_DELETE_REQUEST_METHOD;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getResponseHandlerType()
    {
        return ResponseFactory::PRODUCTS_DELETE_RESPONSE_HANDLER;
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
              'id' => $this->getProduct()->getId()
            ]
        );
    }
}
