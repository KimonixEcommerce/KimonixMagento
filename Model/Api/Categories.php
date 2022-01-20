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
use Kimonix\Kimonix\Model\Api\Data\CategoriesFactory as CategoriesResponseFactory;
use Kimonix\Kimonix\Model\Api\Data\CategoryFactory as CategoryResponseFactory;
use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\App\Emulation;

class Categories extends AbstractApi implements \Kimonix\Kimonix\Api\CategoriesInterface
{
    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var CategoryResponseFactory
     */
    protected $_categoryResponseFactory;

    /**
     * @var CategoriesResponseFactory
     */
    protected $_categoriesResponseFactory;

    /**
     * @method __construct
     * @param  BasicResponseFactory      $basicResponseFactory
     * @param  Request                   $request
     * @param  Emulation                 $appEmulation
     * @param  KimonixConfig             $kimonixConfig
     * @param  CategoryFactory           $categoryFactory
     * @param  CategoryCollectionFactory $categoryCollectionFactory
     * @param  CategoryResponseFactory   $categoryResponseFactory
     * @param  CategoriesResponseFactory $categoriesResponseFactory
     */
    public function __construct(
        BasicResponseFactory $basicResponseFactory,
        Request $request,
        Emulation $appEmulation,
        KimonixConfig $kimonixConfig,
        CategoryFactory $categoryFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryResponseFactory $categoryResponseFactory,
        CategoriesResponseFactory $categoriesResponseFactory
    ) {
        parent::__construct($basicResponseFactory, $request, $appEmulation, $kimonixConfig);
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryResponseFactory = $categoryResponseFactory;
        $this->_categoriesResponseFactory = $categoriesResponseFactory;
    }

    /**
     * Get response factory
     * @return CategoriesResponseFactory
     * @throws \Exception
     */
    protected function getResponseFactory()
    {
        return $this->_categoriesResponseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories($limit = null, $page = null)
    {
        try {
            $this->authGuard();

            $categoryCollection = $this->_categoryCollectionFactory->create()
                ->addFieldToSelect('*');

            if (is_numeric($limit)) {
                $categoryCollection->setPageSize($limit);
            }

            if (is_numeric($page)) {
                $categoryCollection->setCurPage($page);
            }

            $categories = [];
            foreach ($categoryCollection as $category) {
                $_data = $category->getData();
                $_data['kimonix_control'] = $category->getKimonixControl();
                $_data['kimonix_dynamic_fetch'] = $category->getKimonixDynamicFetch();
                $_data['default_sort_by'] = $category->getDefaultSortBy();
                $_data['children_ids'] = $category->getChildren() ? explode(",", $category->getChildren()) : [];
                //$_data['product_ids'] = $category->getProductCollection()->getAllIds();
                $categories[] = $this->_categoryResponseFactory->create()
                    ->setData($_data);
            }

            $this->getResponse()->setCategories($categories);

            $this->setResponseMessage("get_categories_success");
        } catch (\Exception $e) {
            $this->setResponseException($e);
        }

        return $this->getResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function postCategories($categories = [])
    {
        try {
            $this->authGuard();

            if (!$categories) {
                throw new \Exception("empty_categories_nothing_to_do", 1);
            }

            foreach ($categories as $categoryResponse) {
                try {
                    $categoryResponse->setParentId(
                        $categoryResponse->getParentId() ?: $this->_kimonixConfig->getRootCategoryId()
                    );
                    $_parentCategory = $this->_categoryFactory->create()
                        ->load($categoryResponse->getParentId());
                    if (!($_parentCategory && $_parentCategory->getId())) {
                        throw new \Exception("parent_category_does_not_exist", 1);
                    }

                    $_categoryModel = $this->_categoryFactory->create()
                        ->load($categoryResponse->getId());

                    if (!($_categoryModel && $_categoryModel->getId())) {
                        $_categoryModel = $this->_categoryFactory->create();

                        if ($categoryResponse->getIsActive() === null) {
                            $categoryResponse->setIsActive(1);
                        }
                        if ($categoryResponse->getIsAnchor() === null) {
                            $categoryResponse->setIsAnchor(0);
                        }
                        if ($categoryResponse->getDefaultSortBy() === null) {
                            $categoryResponse->setDefaultSortBy('position');
                        }

                        $categoryResponse->setPath($_parentCategory->getPath());
                        $categoryResponse->setLevel(null);

                        $message = 'category_created';
                    } else {
                        if (!$categoryResponse->getPath()) {
                            $categoryResponse->setPath($_parentCategory->getPath() . '/' . $_categoryModel->getId());
                        }
                        $message = 'category_updated';
                    }

                    $_categoryModel
                        ->setData($categoryResponse->getData())
                        ->save();

                    $categoryResponse
                        ->setData($_categoryModel->getData())
                        ->setError(0)
                        ->setMessage($message);
                } catch (\Exception $e) {
                    $categoryResponse->setExceptionData($e, $this->_kimonixConfig->isDebugEnabled());
                }
            }

            $this->getResponse()->setCategories($categories);
            $this->setResponseMessage("post_categories_success");
        } catch (\Exception $e) {
            $this->setResponseException($e);
        }

        return $this->getResponse();
    }
}
