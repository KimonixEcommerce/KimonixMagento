<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Observer\Config;

use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Model\Request\Factory as KimonixRequestFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class Save implements ObserverInterface
{
    /**
     * @var KimonixConfig
     */
    private $kimonixConfig;

    /**
     * @var ReinitableConfigInterface
     */
    private $appConfig;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var KimonixRequestFactory
     */
    private $kimonixRequestFactory;

    /**
     * @method __construct
     * @param  KimonixConfig             $kimonixConfig
     * @param  ReinitableConfigInterface $appConfig
     * @param  TypeListInterface         $cacheTypeList
     * @param  MessageManagerInterface   $messageManager
     * @param  KimonixRequestFactory     $kimonixRequestFactory
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        ReinitableConfigInterface $appConfig,
        TypeListInterface $cacheTypeList,
        MessageManagerInterface $messageManager,
        KimonixRequestFactory $kimonixRequestFactory
    ) {
        $this->kimonixConfig = $kimonixConfig;
        $this->appConfig = $appConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->messageManager = $messageManager;
        $this->kimonixRequestFactory = $kimonixRequestFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $this->cleanConfigCache();

        if ($this->kimonixConfig->isEnabled()) {
            try {
                if (!$this->kimonixConfig->getKimonixApiKey()) {
                    throw new \Exception(
                        __('Kimonix API key is missing!')
                    );
                }

                $this->kimonixRequestFactory
                    ->create(KimonixRequestFactory::SETUP_ACTIVATE_REQUEST_METHOD)
                    ->setBaseUrl($this->kimonixConfig->getBaseUrl())
                    ->setCurrency($this->kimonixConfig->getCurrentStoreCurrency())
                    ->execute();

                $this->kimonixRequestFactory
                    ->create(KimonixRequestFactory::SETUP_STORE_REQUEST_METHOD)
                    ->execute()
                    ->update(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

                return $this->messageManager->addSuccess(__('Kimonix API key is valid! (store has been successfully activated)'));
            } catch (\Exception $e) {
                $this->kimonixConfig->resetCredentials();
                throw new \Exception(__('Kimonix - Failed to activate store! Automatically disabled Kimonix for this scope. [Exception: %1]', $e->getMessage()));
            }
        }
    }

    private function cleanConfigCache()
    {
        try {
            $this->cacheTypeList->cleanType(Config::TYPE_IDENTIFIER);
            $this->appConfig->reinit();
        } catch (\Exception $e) {
            $this->messageManager->addNoticeMessage(__('For some reason, Kimonix couldn\'t clear your config cache, please clear the cache manually. (Exception message: %1)', $e->getMessage()));
        }
        return $this;
    }
}
