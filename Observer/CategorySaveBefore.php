<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Observer;

use Magento\Framework\Event\ObserverInterface;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Model\Request\Factory as KimonixRequestFactory;
use Magento\Framework\Event\Observer;

class CategorySaveBefore implements ObserverInterface
{
    /**
     * @var KimonixConfig
     */
    private $kimonixConfig;

    /**
     * @var KimonixRequestFactory
     */
    private $kimonixRequestFactory;

    /**
     * @method __construct
     * @param  KimonixConfig         $kimonixConfig
     * @param  KimonixRequestFactory $kimonixRequestFactory
     */
    public function __construct(
        KimonixConfig $kimonixConfig,
        KimonixRequestFactory $kimonixRequestFactory
    ) {
        $this->kimonixConfig = $kimonixConfig;
        $this->kimonixRequestFactory = $kimonixRequestFactory;
    }

    public function execute(Observer $observer)
    {
        if ($this->kimonixConfig->isEnabled()) {
            try {
                $category = $observer->getEvent()->getCategory();

                if (!$category->isObjectNew()) {
                    if ($category->getOrigData('name') !== $category->getData('name') ||
                        $category->getOrigData('description') !== $category->getData('description') ||
                        $category->getOrigData('url_key') !== $category->getData('url_key') ||
                        $category->getOrigData('parent_id') !== $category->getData('parent_id')
                    ) {
                        $request = $this->kimonixRequestFactory
                            ->create(KimonixRequestFactory::CATEGORIES_UPDATE_REQUEST_METHOD)
                            ->setCategory($category)
                            ->execute();
                    }
                }

            } catch (\Exception $e) {
                $this->kimonixConfig->log("[Kimonix - CategoryUpdateBefore - ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString(), "error");
                throw $e;
            }
        }
    }
}
