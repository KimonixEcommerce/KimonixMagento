<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Block;

use Magento\Framework\View\Element\Template;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Magento\Framework\View\Element\Template\Context;

class AbstractBlock extends Template
{
    /**
     * @var KimonixConfig
     */
    protected $_kimonixConfig;

    /**
     * @method __construct
     * @param Context $context
     * @param KimonixConfig $kimonixConfig
     */
    public function __construct(
        Context $context,
        KimonixConfig $kimonixConfig
    ) {
        parent::__construct($context);
        $this->_kimonixConfig = $kimonixConfig;
    }

    public function isEnabled()
    {
        return $this->_kimonixConfig->isEnabled();
    }

    public function getKimonixStoreId()
    {
        return $this->_kimonixConfig->getKimonixStoreId();
    }

    public function getKimonixApiUrl()
    {
        return $this->_kimonixConfig->getKimonixApiUrl();
    }

    public function isDebugEnabled()
    {
        return $this->_kimonixConfig->isDebugEnabled();
    }
}
