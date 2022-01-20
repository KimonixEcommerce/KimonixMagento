<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Api\Data;

/**
 * Interface BasicResponseInterface
 * @api
 */
interface BasicResponseInterface
{
    /*
     * error
     */
    const ERROR = 'error';

    /*
     * message
     */
    const MESSAGE = 'message';

    /*
     * trace
     */
    const TRACE = 'trace';

    /**
     * @return int|null $error.
     */
    public function getError();

    /**
     * @param int|null $error
     * @return $this
     */
    public function setError($error);

    /**
     * @return string|null $message.
     */
    public function getMessage();

    /**
     * @param string|null $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return string|null $trace.
     */
    public function getTrace();

    /**
     * @param string|null $trace
     * @return $this
     */
    public function setTrace($trace);
}
