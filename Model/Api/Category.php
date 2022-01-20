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
use Kimonix\Kimonix\Model\Api\Data\CategoryFactory as CategoryResponseFactory;
use Kimonix\Kimonix\Model\Api\Data\ProductFactory as ProductResponseFactory;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\App\Emulation;

class Category extends AbstractApi implements \Kimonix\Kimonix\Api\CategoryInterface
{
    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var ProductStatus
     */
    private $_productStatus;

    /**
     * @var ProductVisibility
     */
    private $_productVisibility;

    /**
     * @var CategoryResponseFactory
     */
    protected $_categoryResponseFactory;

    /**
     * @var ProductResponseFactory
     */
    protected $_productResponseFactory;

    /**
     * @method __construct
     * @param  BasicResponseFactory    $basicResponseFactory
     * @param  Request                 $request
     * @param  Emulation               $appEmulation
     * @param  KimonixConfig           $kimonixConfig
     * @param  CategoryFactory         $categoryFactory
     * @param  ProductStatus           $productStatus
     * @param  ProductVisibility       $productVisibility
     * @param  CategoryResponseFactory $categoryResponseFactory
     * @param  ProductResponseFactory  $productResponseFactory
     */
    public function __construct(
        BasicResponseFactory $basicResponseFactory,
        Request $request,
        Emulation $appEmulation,
        KimonixConfig $kimonixConfig,
        CategoryFactory $categoryFactory,
        ProductStatus $productStatus,
        ProductVisibility $productVisibility,
        CategoryResponseFactory $categoryResponseFactory,
        ProductResponseFactory $productResponseFactory
    ) {
        parent::__construct($basicResponseFactory, $request, $appEmulation, $kimonixConfig);
        $this->_categoryFactory = $categoryFactory;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        $this->_categoryResponseFactory = $categoryResponseFactory;
        $this->_productResponseFactory = $productResponseFactory;
    }

    /**
     * Get response factory
     * @return ProductsResponseFactory
     * @throws \Exception
     */
    protected function getResponseFactory()
    {
        return $this->_categoryResponseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts($id)
    {
        try {
            $this->authGuard();

            $category = $this->_categoryFactory->create()
                ->load($id);

            if (!($category && $category->getId())) {
                throw new \Exception("category_does_not_exist", 1);
            }

            $productsCollection = $category->getProductCollection();
            $productsCollection->addAttributeToSelect(['entity_id', 'name', 'status']);
            //$productsCollection->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);
            $productsCollection->setVisibility($this->_productVisibility->getVisibleInCatalogIds());
            $productsCollection->addAttributeToSort('status', 'DESC');
            $productsCollection->addAttributeToSort('position', 'ASC');

            $products = [];
            foreach ($productsCollection as $product) {
                $products[] = $this->_productResponseFactory->create()
                    ->setData([
                        "entity_id" => $product->getId(),
                        "name" => $product->getName(),
                        "is_active" => (bool) in_array($product->getStatus(), $this->_productStatus->getVisibleStatusIds()),
                    ]);
            }

            $this->getResponse()->setEntityId($category->getId());
            $this->getResponse()->setProducts($products);

            $this->setResponseMessage("get_product_categories_success");
        } catch (\Exception $e) {
            $this->setResponseException($e);
        }

        return $this->getResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function postProducts($id, $product_ids = [])
    {
        try {
            $this->authGuard();

            $category = $this->_categoryFactory->create()
                ->load($id);

            if (!($category && $category->getId())) {
                throw new \Exception("category_does_not_exist", 1);
            }

            $productPositions = \array_flip(\array_filter(\array_unique($product_ids)));

            $category
                ->setPostedProducts($productPositions)
                ->setIsAnchor(0)
                ->setDefaultSortBy('position')
                ->save();

            $this->setResponseMessage("post_product_categories_success");
        } catch (\Exception $e) {
            $this->setResponseException($e);
        }

        return $this->getResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function setKimonixAttributes($id, $kimonixControl, $dynamicFetch = null)
    {
        try {
            $this->authGuard();

            if (!in_array($kimonixControl, ['enable', 'disable'])) {
                throw new \Exception(sprintf("invalid_url_param:%s", $kimonixControl), 1);
            }

            $category = $this->_categoryFactory->create()
                ->load($id);

            if (!($category && $category->getId())) {
                throw new \Exception("category_does_not_exist", 1);
            }

            $category->setKimonixControl($kimonixControl === 'enable' ? 1 : 0);

            if ($dynamicFetch !== null) {
                $category->setKimonixDynamicFetch((int)(bool) $dynamicFetch);
            }

            $category->save();

            $this->setResponseMessage(sprintf('category_control_%s_success', $kimonixControl));
        } catch (\Exception $e) {
            $this->setResponseException($e);
        }

        return $this->getResponse();
    }
}
