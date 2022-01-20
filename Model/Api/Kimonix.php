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
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\App\Emulation;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;

class Kimonix extends AbstractApi implements \Kimonix\Kimonix\Api\KimonixInterface
{
    /**
     * @var array
     */
    private $entityTypes = [
        'orders',
        'products',
    ];

    /**
     * @var ReinitableConfigInterface
     */
    private $_appConfig;

    /**
     * @var TypeListInterface
     */
    private $_cacheTypeList;

    /**
     * @method __construct
     * @param  BasicResponseFactory      $basicResponseFactory
     * @param  Request                   $request
     * @param  Emulation                 $appEmulation
     * @param  KimonixConfig             $kimonixConfig
     * @param  ReinitableConfigInterface $appConfig
     * @param  TypeListInterface         $cacheTypeList
     */
    public function __construct(
        BasicResponseFactory $basicResponseFactory,
        Request $request,
        Emulation $appEmulation,
        KimonixConfig $kimonixConfig,
        ReinitableConfigInterface $appConfig,
        TypeListInterface $cacheTypeList
    ) {
        parent::__construct($basicResponseFactory, $request, $appEmulation, $kimonixConfig);
        $this->_appConfig = $appConfig;
        $this->_cacheTypeList = $cacheTypeList;
    }

    /**
     * {@inheritdoc}
     */
    public function resetSyncFlags($entityType = null)
    {
        try {
            $this->authGuard();

            $entityType = $entityType ?: 'all';
            $entities = $entityType === 'all' ? $this->entityTypes : [$entityType];
            foreach ($entities as $entity) {
                if (!in_array($entity, $this->entityTypes)) {
                    throw new \Exception('Entity `' . (string) $entity . '` is not allowed.');
                }
                $this->getObjectManager()
                    ->create(\Kimonix\Kimonix\Model\Jobs\ResetSyncFlags::class)
                    ->execute($entity);
            }

            $this->setResponseMessage("reset" . ($entityType ? "_{$entityType}_": "_") . "sync_flags_success");
        } catch (\Exception $e) {
            $this->setResponseException($e);
        }

        return $this->getResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function setKimonixApiUrl($apiUrl = null)
    {
        try {
            $this->authGuard();
            $this->_kimonixConfig->updateKimonixApiUrl($apiUrl);
            $this->cleanConfigCache();
            $this->setResponseMessage("set_kimonix_api_url_success");
        } catch (\Exception $e) {
            $this->setResponseException($e);
        }

        return $this->getResponse();
    }

    private function cleanConfigCache()
    {
        try {
            $this->_cacheTypeList->cleanType(Config::TYPE_IDENTIFIER);
            $this->_appConfig->reinit();
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Kimonix changes are saved, but for some reason, it couldn\'t clear the config cache. Please clear the cache manually. (Exception message: %s)', $e->getMessage()));
        }
        return $this;
    }
}
