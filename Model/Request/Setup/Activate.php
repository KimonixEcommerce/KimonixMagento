<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Request\Setup;

use Kimonix\Kimonix\Model\AbstractRequest;
use Kimonix\Kimonix\Model\Request\Factory as RequestFactory;
use Kimonix\Kimonix\Model\Response\Factory as ResponseFactory;

/**
 * Kimonix activate request model.
 */
class Activate extends AbstractRequest
{
    /**
     * @var string
     */
    protected $_baseUrl;

    /**
     * @var string
     */
    protected $_currency;

    /**
     * @param  string   $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = (string) $baseUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /**
     * @param  string   $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->_currency = (string) $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
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
        return RequestFactory::SETUP_ACTIVATE_REQUEST_METHOD;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getResponseHandlerType()
    {
        return ResponseFactory::SETUP_ACTIVATE_RESPONSE_HANDLER;
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
              'url' => $this->getBaseUrl(),
              'currency' => $this->getCurrency()
            ]
        );
    }
}
