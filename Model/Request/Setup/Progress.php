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
 * Kimonix setup progress request model.
 */
class Progress extends AbstractRequest
{
    /**
     * @var float
     */
    protected $_progress;

    /**
     * @var string
     */
    protected $_stage;

    /**
     * @param  float   $progress
     * @return $this
     */
    public function setProgress($progress)
    {
        $progress = (float) $progress;
        if ($progress > 1) {
            $progress = 1;
        }
        if ($progress < 0) {
            $progress = 0;
        }
        $this->_progress = $progress;
        return $this;
    }

    /**
     * @return string
     */
    public function getProgress()
    {
        return $this->_progress;
    }

    /**
     * @param  string   $stage
     * @return $this
     */
    public function setStage($stage)
    {
        $this->_stage = $stage;
        return $this;
    }

    /**
     * @return string
     */
    public function getStage()
    {
        return $this->_stage;
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
        return RequestFactory::SETUP_PROGRESS_REQUEST_METHOD;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getResponseHandlerType()
    {
        return ResponseFactory::SETUP_PROGRESS_RESPONSE_HANDLER;
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
              'extension_setup_progress' => $this->getProgress(),
              'extension_setup_stage' => $this->getStage()
            ]
        );
    }
}
