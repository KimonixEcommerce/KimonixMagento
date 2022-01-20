<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Request;

use Kimonix\Kimonix\Model\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Kimonix request factory model.
 */
class Factory
{

    /**
     * Request methods.
     */
    const SETUP_ACTIVATE_REQUEST_METHOD = 'activate';
    const SETUP_STORE_REQUEST_METHOD = 'store';
    const SETUP_PROGRESS_REQUEST_METHOD = 'update_extension_setup_progress';
    const SETUP_FINISHED_REQUEST_METHOD = 'extension_setup_finished';
    const ORDERS_CREATE_REQUEST_METHOD = 'orders/create';
    const ORDERS_UPDATE_REQUEST_METHOD = 'orders/update';
    const ORDERS_UPSERT_REQUEST_METHOD = 'orders/upsert';
    const ORDERS_DELETE_REQUEST_METHOD = 'orders/delete';
    const PRODUCTS_CREATE_REQUEST_METHOD = 'products/create';
    const PRODUCTS_UPDATE_REQUEST_METHOD = 'products/update';
    const PRODUCTS_UPSERT_REQUEST_METHOD = 'products/upsert';
    const PRODUCTS_DELETE_REQUEST_METHOD = 'products/delete';
    const CATEGORIES_UPDATE_REQUEST_METHOD = 'categories/update';
    const CATEGORIES_DELETE_REQUEST_METHOD = 'categories/delete';

    /**
     * Map request methods to corresponding classes.
     *
     * @var array
     */
    private $methodClasses = [
        self::SETUP_ACTIVATE_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Setup\Activate::class,
        self::SETUP_STORE_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Setup\Store::class,
        self::SETUP_PROGRESS_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Setup\Progress::class,
        self::SETUP_FINISHED_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Setup\Finished::class,
        self::ORDERS_CREATE_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Orders\Create::class,
        self::ORDERS_UPDATE_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Orders\Update::class,
        self::ORDERS_UPSERT_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Orders\Upsert::class,
        self::ORDERS_DELETE_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Orders\Delete::class,
        self::PRODUCTS_CREATE_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Products\Create::class,
        self::PRODUCTS_UPDATE_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Products\Update::class,
        self::PRODUCTS_UPSERT_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Products\Upsert::class,
        self::PRODUCTS_DELETE_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Products\Delete::class,
        self::CATEGORIES_UPDATE_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Categories\Update::class,
        self::CATEGORIES_DELETE_REQUEST_METHOD => \Kimonix\Kimonix\Model\Request\Categories\Delete::class,
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
     * Create request model.
     *
     * @param string       $method
     *
     * @return RequestInterface
     * @throws LocalizedException
     */
    public function create($method)
    {
        if (!empty($this->methodClasses[$method])) {
            $className = $this->methodClasses[$method];
        } else {
            throw new LocalizedException(
                __('%s method is not supported.', $method)
            );
        }

        $model = $this->getObjectManager()->create($className);

        if (!$model instanceof RequestInterface) {
            throw new LocalizedException(
                __(
                    '%1 doesn\'t implement \Kimonix\Kimonix\Model\RequestInterface',
                    $className
                )
            );
        }

        return $model;
    }
}
