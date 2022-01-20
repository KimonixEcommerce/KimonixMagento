<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Api\Data;

use Kimonix\Kimonix\Api\Data\BasicResponseInterface;
use Magento\Framework\Model\AbstractModel;

class BasicResponse extends AbstractModel implements BasicResponseInterface
{
    /**
     * {@inheritdoc}
     */
    public function setError($error)
    {
        return $this->setData(self::ERROR, $error);
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        return $this->getData(self::ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrace($trace)
    {
        return $this->setData(self::TRACE, $trace);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrace()
    {
        return $this->getData(self::TRACE);
    }

    /**
     * Set exception
     * @param  \Exception  $e
     * @param  bool        $withTrace
     * @return $this
     */
    public function setExceptionData(\Exception $e, $withTrace = false)
    {
        $this->setError(1);
        $this->setMessage($e->getMessage());
        if ($withTrace) {
            $this->setTrace($e->getTraceAsString());
        }
        return $this;
    }
}
