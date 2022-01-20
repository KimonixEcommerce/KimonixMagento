<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Request\Products;

use Kimonix\Kimonix\Model\AbstractRequest;
use Kimonix\Kimonix\Model\Request\Factory as RequestFactory;
use Kimonix\Kimonix\Model\Response\Factory as ResponseFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Model\Schema as KimonixSchema;
use Kimonix\Kimonix\Lib\Http\Client\Curl;

/**
 * Kimonix products/update request model.
 */
class Update extends AbstractRequest
{
    /**
     * @var null|array
     */
    private $preparedParams;

    /**
     * @var int
     */
    private $preparedProductsCount = 0;

    /**
     * @var int
     */
    private $skippedProductsCount = 0;

    /**
     * @var ProductCollection
     */
    private $productsCollection;

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
     * @method setProductsCollection
     * @param  ProductCollection     $productsCollection
     * @return AbstractRequest
     */
    public function setProductsCollection(ProductCollection $productsCollection)
    {
        $this->productsCollection = $productsCollection;
        return $this;
    }

    /**
     * @method getProductsCollection
     * @return ProductCollection
     */
    public function getProductsCollection()
    {
        return $this->productsCollection;
    }

    /**
     * Check if request can be executed
     * @return bool
     */
    public function canExecute()
    {
        return parent::canExecute() && (bool) $this->getPreparedProductsCount();
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
        $this->preparedProductsCount = 0;
        $this->skippedProductsCount = 0;
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

        foreach ($this->getProductsCollection() as $product) {
            try {
                $this->_kimonixConfig->log("Request\Products\Update::getParams() preparing product ID: {$product->getId()} ...", 'debug');
                $this->preparedParams[] = $this->kimonixSchema->getProductSchema($product);
                $this->preparedProductsCount++;
            } catch (\Exception $e) {
                $this->_kimonixConfig->log("Request\Products\Update::getParams()- Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'debug');
                $this->skippedProductsCount++;
            }
        }

        return $this->preparedParams;
    }

    /**
     * @return int
     */
    public function getPreparedProductsCount()
    {
        return $this->preparedProductsCount;
    }

    /**
     * @return int
     */
    public function getSkippedProductsCount()
    {
        return $this->skippedProductsCount;
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
        return RequestFactory::PRODUCTS_UPDATE_REQUEST_METHOD;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getResponseHandlerType()
    {
        return ResponseFactory::PRODUCTS_UPDATE_RESPONSE_HANDLER;
    }
}
