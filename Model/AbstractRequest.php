<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model;

use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Lib\Http\Client\Curl;
use Kimonix\Kimonix\Model\Response\Factory as ResponseFactory;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Kimonix abstract request model.
 */
abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var bool
     */
    protected $_checkAllowDataSending = false;

    /**
     * @var KimonixConfig
     */
    protected $_kimonixConfig;

    /**
     * @var Curl
     */
    protected $_curl;

    /**
     * @var ResponseInterface
     */
    protected $_responseFactory;

    /**
     * Object constructor.
     *
     * @param KimonixConfig   $kimonixConfig
     * @param Curl            $curl
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        Curl $curl,
        ResponseFactory $responseFactory
    ) {
        $this->_kimonixConfig = $kimonixConfig;
        $this->_curl = $curl;
        $this->_responseFactory = $responseFactory;
    }

    /**
     * @param  null|OutputInterface $output
     * @return AbstractResponse
     */
    public function execute($output = null)
    {
        if ($this->canExecute()) {
            $this->sendRequest();

            return $this
                ->getResponseHandler()
                ->process($output);
        } else {
            throw new \Exception("Request can not be executed.");
        }
    }

    /**
     * Check if request can be executed
     * @return bool
     */
    protected function canExecute()
    {
        if ($this->_checkAllowDataSending && !$this->_kimonixConfig->getAllowDataSending()) {
            throw new \Exception("Request can not be executed. Kimonix data sending is not allowed for this store.");
        }
        return true;
    }

    /**
     * Return full endpoint to particular method for request call.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        return $this->_kimonixConfig->getKimonixApiUrl($this->getRequestMethod());
    }

    /**
     * Return method for request call.
     *
     * @return string
     */
    abstract protected function getRequestMethod();

    /**
     * Return response handler type.
     *
     * @return string
     */
    abstract protected function getResponseHandlerType();

    /**
     * @return array
     */
    protected function getParams()
    {
        return [];
    }

    /**
     * @return string
     */
    protected function getCurlMethod()
    {
        return 'post';
    }

    /**
     * @return AbstractRequest
     */
    protected function sendRequest()
    {
        $endpoint = $this->getEndpoint();
        $params = $this->getParams();

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->_kimonixConfig->getKimonixApiKey(),
        ];
        $this->_curl->setHeaders($headers);

        $this->_kimonixConfig->log('AbstractRequest::sendRequest() ', 'debug', [
            'method' => $this->getRequestMethod(),
            'request' => [
                'Type' => $this->getCurlMethod(),
                'Endpoint' => $endpoint,
                'Headers' => $headers,
                'Params' => $params
            ],
        ]);

        $this->_curl->{$this->getCurlMethod()}($endpoint, $params);

        return $this;
    }

    /**
     * Return response handler.
     *
     * @return ResponseInterface
     */
    protected function getResponseHandler()
    {
        $responseHandler = $this->_responseFactory->create(
            $this->getResponseHandlerType(),
            $this->_curl
        );

        return $responseHandler;
    }
}
