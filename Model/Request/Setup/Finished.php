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
 * Kimonix setup finished request model.
 */
class Finished extends AbstractRequest
{
    /**
     * @return string
     */
    protected function getCurlMethod()
    {
        return 'get';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getRequestMethod()
    {
        return RequestFactory::SETUP_FINISHED_REQUEST_METHOD;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getResponseHandlerType()
    {
        return ResponseFactory::SETUP_FINISHED_RESPONSE_HANDLER;
    }
}
