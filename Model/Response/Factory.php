<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Response;

use Kimonix\Kimonix\Lib\Http\Client\Curl;
use Kimonix\Kimonix\Model\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;

/**
 * Kimonix response factory model.
 */
class Factory
{
    /**
     * Response handlers.
     */
    const SETUP_ACTIVATE_RESPONSE_HANDLER = 'activate';
    const SETUP_STORE_RESPONSE_HANDLER = 'store';
    const SETUP_PROGRESS_RESPONSE_HANDLER = 'update_extension_setup_progress';
    const SETUP_FINISHED_RESPONSE_HANDLER = 'extension_setup_finished';
    const ORDERS_CREATE_RESPONSE_HANDLER = 'orders/create';
    const ORDERS_UPDATE_RESPONSE_HANDLER = 'orders/update';
    const ORDERS_UPSERT_RESPONSE_HANDLER = 'orders/upsert';
    const ORDERS_DELETE_RESPONSE_HANDLER = 'orders/delete';
    const PRODUCTS_CREATE_RESPONSE_HANDLER = 'products/create';
    const PRODUCTS_UPDATE_RESPONSE_HANDLER = 'products/update';
    const PRODUCTS_UPSERT_RESPONSE_HANDLER = 'products/upsert';
    const PRODUCTS_DELETE_RESPONSE_HANDLER = 'products/delete';
    const CATEGORIES_UPDATE_RESPONSE_HANDLER = 'categories/update';
    const CATEGORIES_DELETE_RESPONSE_HANDLER = 'categories/delete';

    /**
     * Map response handlers to corresponding classes.
     *
     * @var array
     */
    private $handlerClasses = [
        self::SETUP_ACTIVATE_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Setup\Activate::class,
        self::SETUP_STORE_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Setup\Store::class,
        self::SETUP_PROGRESS_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Setup\Progress::class,
        self::SETUP_FINISHED_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Setup\Finished::class,
        self::ORDERS_CREATE_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Orders\Create::class,
        self::ORDERS_UPDATE_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Orders\Update::class,
        self::ORDERS_UPSERT_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Orders\Upsert::class,
        self::ORDERS_DELETE_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Orders\Delete::class,
        self::PRODUCTS_CREATE_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Products\Create::class,
        self::PRODUCTS_UPDATE_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Products\Update::class,
        self::PRODUCTS_UPSERT_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Products\Upsert::class,
        self::PRODUCTS_DELETE_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Products\Delete::class,
        self::CATEGORIES_UPDATE_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Categories\Update::class,
        self::CATEGORIES_DELETE_RESPONSE_HANDLER => \Kimonix\Kimonix\Model\Response\Categories\Delete::class,
    ];

    /**
     * @method getObjectManager
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Create response model.
     *
     * @param string            $type
     * @param Curl|null         $curl
     * @param OrderPayment|null $payment
     *
     * @return ResponseInterface
     * @throws LocalizedException
     */
    public function create(
        $type,
        $curl = null
    ) {
        if (!empty($this->handlerClasses[$type])) {
            $className = $this->handlerClasses[$type];
        } else {
            throw new LocalizedException(
                __('%s handler type is not supported.', $type)
            );
        }

        $model = $this->getObjectManager()->create(
            $className,
            [
                'curl' => $curl
            ]
        );
        if (!$model instanceof ResponseInterface) {
            throw new LocalizedException(
                __(
                    '%1 doesn\'t implement \Kimonix\Kimonix\Model\ResponseInterface',
                    $className
                )
            );
        }

        return $model;
    }
}
