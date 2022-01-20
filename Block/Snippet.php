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

use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;

class Snippet extends AbstractBlock
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Registry
     */
    private $_registry;

    /**
     * @method __construct
     * @param  Context          $context
     * @param  KimonixConfig    $kimonixConfig
     * @param  RequestInterface $request
     * @param  Registry         $registry
     */
    public function __construct(
        Context $context,
        KimonixConfig $kimonixConfig,
        RequestInterface $request,
        Registry $registry
    ) {
        parent::__construct($context, $kimonixConfig);
        $this->_request = $request;
        $this->_registry = $registry;
    }

    public function getKimonixJsUrl()
    {
        if ($this->getKimonixStoreId()) {
            return $this->getKimonixApiUrl() . '/kimonix_magento.js?store_id=' . $this->getKimonixStoreId();
        }
    }

    public function getKimonixCollectStoreEventUrl()
    {
        if ($this->getKimonixStoreId()) {
            return $this->getKimonixApiUrl() . '/collect_store_event?store_id=' . $this->getKimonixStoreId();
        }
    }

    public function getPageType()
    {
        /*if ($this->_request->getFullActionName() == 'catalog_product_view') {
            return 'product_view';
        }
        if ($this->_request->getFullActionName() == 'catalog_category_view') {
            return 'category_view';
        }
        return "other";*/
        return $this->_request->getFullActionName();
    }

    public function getProduct()
    {
        if ($this->getData('product') === null) {
            $this->setData('product', $this->_registry->registry('current_product'));
        }
        return $this->getData('product');
    }

    public function hasProduct()
    {
        return $this->getProduct() && $this->getProduct()->getId();
    }

    public function getProductId()
    {
        if (!$this->hasProduct()) {
            return null;
        }
        return $this->getProduct()->getId();
    }

    public function getCategory()
    {
        if ($this->getData('category') === null) {
            $this->setData('category', $this->_registry->registry('current_category'));
        }
        return $this->getData('category');
    }

    public function hasCategory()
    {
        return $this->getCategory() && $this->getCategory()->getId();
    }

    public function getCategoryId()
    {
        if (!$this->hasCategory()) {
            return null;
        }
        return $this->getCategory()->getId();
    }

    public function getSnippetJsonData()
    {
        $data = [
            "store_id" => $this->getKimonixStoreId(),
            "type" => $this->getPageType(),
            "product_id" => (int) $this->getProductId() ?: null,
            "category_id" => (int) $this->getCategoryId() ?: null,
            "url_path" => isset($_SERVER["REQUEST_URI"])? strtok($_SERVER["REQUEST_URI"], '?') : null,
            "full_url" => $this->_kimonixConfig->getUrlBuilder()->getCurrentUrl()
        ];
        if ($data["type"] === 'product_view') {
            $data["url_path"] = preg_replace('/'. preg_quote($this->_kimonixConfig->getProductUrlSuffix(), '/') . '$/', '', $data["url_path"]);
        } elseif ($data["type"] === 'category_view') {
            $data["url_path"] = preg_replace('/'. preg_quote($this->_kimonixConfig->getCategoryUrlSuffix(), '/') . '$/', '', $data["url_path"]);
        }
        return json_encode($data);
    }
}
