<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Api;

use Kimonix\Kimonix\Model\Api\Data\BasicResponseFactory;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Magento\Framework\App\Area;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\App\Emulation;

class AbstractApi
{
    /**
     * @var BasicResponseFactory
     */
    protected $_basicResponseFactory;

    /**
     * @var Request
     */
    protected $_request;

    /**
     * @var Emulation
     */
    protected $_appEmulation;

    /**
     * @var KimonixConfig
     */
    protected $_kimonixConfig;

    /**
     * @var string
     */
    private $authApiKey;

    /**
     * @var array|null
     */
    protected $_requestParams;

    /**
     * @var BasicResponse
     */
    protected $_response;

    /**
     * @method __construct
     * @param  BasicResponseFactory $basicResponseFactory
     * @param  Request              $request
     * @param  Emulation            $appEmulation
     * @param  KimonixConfig        $kimonixConfig
     */
    public function __construct(
        BasicResponseFactory $basicResponseFactory,
        Request $request,
        Emulation $appEmulation,
        KimonixConfig $kimonixConfig
    ) {
        $this->_basicResponseFactory = $basicResponseFactory;
        $this->_request = $request;
        $this->_appEmulation = $appEmulation;
        $this->_kimonixConfig = $kimonixConfig;
    }

    /**
     * Check if request is authorized
     * @return bool
     * @throws \Exception
     */
    protected function authGuard()
    {
        $this->emulateAdminArea();
        if ($this->_kimonixConfig->isEnabled() &&
            ($apiKey = $this->_kimonixConfig->getKimonixApiKey()) &&
            $apiKey === $this->getAuthApiKey()
        ) {
            return true;
        }
        throw new \Exception('Access Denied!');
    }

    /**
     * Get API key from request headers
     * @return string
     */
    protected function getAuthApiKey()
    {
        if ($this->authApiKey === null) {
            $this->authApiKey = trim($this->_request->getHeader("Authorization"));
            $this->authApiKey = substr($this->authApiKey, strrpos($this->authApiKey, ' ') + 1);
        }
        return $this->authApiKey;
    }

    /**
     * Get request
     * @return Request
     */
    protected function getRequest()
    {
        return $this->_request;
    }

    /**
     * Get request
     * @param  string|null $key
     * @return Request
     */
    protected function getRequestParams($key = null)
    {
        if ($this->_requestParams === null) {
            $this->_requestParams = $this->getRequest()->getParams();
        }
        if ($key && isset($this->_requestParams[$key])) {
            return $this->_requestParams[$key];
        }
        return $this->_requestParams;
    }

    /**
     * Get response factory
     * @return BasicResponseInterface
     * @throws \Exception
     */
    protected function getResponseFactory()
    {
        return $this->_basicResponseFactory;
    }

    /**
     * Get response object
     * @return $this
     * @throws \Exception
     */
    protected function getResponse()
    {
        if ($this->_response === null) {
            $this->_response = $this->getResponseFactory()->create()
                ->setData(["error" => 0,"message" => ""]);
        }
        return $this->_response;
    }

    /**
     * Set response message
     * @param  string  $message
     * @return $this
     */
    protected function setResponseMessage($message)
    {
        $this->getResponse()->setMessage((string) $message);
        return $this;
    }

    /**
     * Set response exception
     * @param  \Exception  $e
     * @return $this
     */
    protected function setResponseException(\Exception $e)
    {
        $this->stopEnvironmentEmulation();
        $this->getResponse()->setExceptionData($e, $this->_kimonixConfig->isDebugEnabled());
        return $this;
    }

    /**
     * Stop environment emulation
     * @return $this
     */
    protected function stopEnvironmentEmulation()
    {
        $this->_appEmulation->stopEnvironmentEmulation();
        return $this;
    }

    /**
     * Start admin environment emulation
     * @return $this
     */
    protected function emulateAdminArea()
    {
        $this->_appEmulation->startEnvironmentEmulation(
            \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            Area::AREA_ADMINHTML,
            true
        );
        return $this;
    }

    /**
     * @method getObjectManager
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }
}
