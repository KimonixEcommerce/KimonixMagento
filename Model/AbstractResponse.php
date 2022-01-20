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
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Kimonix abstract response model.
 */
abstract class AbstractResponse implements ResponseInterface
{
    /**
     * @var KimonixConfig
     */
    protected $_kimonixConfig;

    /**
     * @var Curl
     */
    protected $_curl;

    /**
     * @var int
     */
    protected $_status;

    /**
     * @var array
     */
    protected $_headers;

    /**
     * @var array
     */
    protected $_body;

    /**
     * @var array
     */
    protected $_innerBody;

    /**
     * @method __construct
     * @param  KimonixConfig $kimonixConfig
     * @param  Curl          $curl
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        Curl $curl
    ) {
        $this->_kimonixConfig = $kimonixConfig;
        $this->_curl = $curl;
    }

    /**
     * @param  null|OutputInterface $output
     * @return AbstractResponse
     */
    public function process($output = null)
    {
        $responseData = $this->prepareResponseData();
        $requestStatus = $this->getRequestStatus();
        $this->processResponseOutput($output, $responseData);

        $this->_kimonixConfig->log('AbstractResponse::process() ', 'debug', [
            'response' => $responseData,
            'status' => $requestStatus === true ? 'success' : 'failure',
        ]);

        if ($requestStatus === false) {
            throw new LocalizedException($this->getErrorMessage());
        }

        $this->validateResponseData();

        return $this;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getErrorMessage()
    {
        $errorReason = $this->getErrorReason();
        if ($errorReason !== false && $this->_kimonixConfig->isDebugEnabled()) {
            return __('Request to Kimonix API failed. Details: "%1".', $errorReason);
        }

        return __('Request to Kimonix API failed.');
    }

    /**
     * @return bool
     */
    protected function getErrorReason()
    {
        $body = $this->getBody();
        if (is_array($body) && !empty($body['error'])) {
            if (!empty($body['error_details'])) {
                return (string) $body['error'] . " | " . (string) $body['error_details'];
            } else {
                return (string) $body['error'];
            }
        }
        return false;
    }

    /**
     * Determine if request succeed or failed.
     *
     * @return bool
     */
    protected function getRequestStatus()
    {
        $httpStatus = $this->getStatus();
        if (!in_array($httpStatus, [200, 201])) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    protected function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @return int
     */
    protected function getStatus()
    {
        if ($this->_status === null) {
            $this->_status = $this->_curl->getStatus();
        }

        return $this->_status;
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        if ($this->_headers === null) {
            $this->_headers = $this->_curl->getHeaders();
        }

        return $this->_headers;
    }

    /**
     * @return array
     */
    protected function getBody()
    {
        if ($this->_body === null) {
            $body = $this->_curl->getBody();
            $this->_body = (array) json_decode($body, 1);
            if ($body && !$this->_body) {
                parse_str($body, $this->_body);
            }
        }

        return $this->_body;
    }

    /**
     * @method getInnerBody
     * @return array
     */
    protected function getInnerBody()
    {
        if ($this->_innerBody === null) {
            $this->_innerBody = $this->getBody();
            if (($ikey = $this->getInnerBodyKey())) {
                if (isset($this->_innerBody[$ikey])) {
                    $this->_innerBody = $this->_innerBody[$ikey];
                } else {
                    throw new LocalizedException(
                        __('Kimonix missing required response field: %s.', $ikey)
                    );
                }
            }
        }

        return $this->_innerBody;
    }

    /**
     * @return array
     */
    protected function prepareResponseData()
    {
        return [
            'status' => $this->getStatus(),
            'headers' => $this->getHeaders(),
            'body' => $this->getBody(),
        ];
    }

    protected function getInnerBodyKey()
    {
        return false;
    }

    /**
     * @return AbstractResponse
     */
    protected function validateResponseData()
    {
        $requiredKeys = $this->getRequiredResponseDataKeys();
        $bodyKeys = array_keys($this->getInnerBody());
        $diff = array_diff($requiredKeys, $bodyKeys);
        if (!empty($diff)) {
            throw new LocalizedException(
                __(
                    'Kimonix required response data fields are missing: %s.',
                    implode(', ', $diff)
                )
            );
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getRequiredResponseDataKeys()
    {
        return [];
    }

    /**
     * @method getResponseData
     * @return DataObject
     */
    public function getResponseData()
    {
        return new DataObject($this->getResponseData());
    }

    /**
     * @param  null|OutputInterface $output
     * @param  array                $responseData
     * @return AbstractResponse
     */
    protected function processResponseOutput(OutputInterface $output = null, $responseData = [])
    {
        if ($output instanceof OutputInterface &&
            ($responseData = $responseData ?: $this->prepareResponseData())
        ) {
            $output->writeln('<comment>Response:</comment>');
            foreach ($responseData as $key => $value) {
                $output->writeln('<comment>  [' . \ucfirst($key) . ']: ' . json_encode($value) . '</comment>');
            }
        }

        return $this;
    }
}
