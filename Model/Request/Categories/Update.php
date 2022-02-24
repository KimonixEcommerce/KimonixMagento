<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Request\Categories;

use Kimonix\Kimonix\Model\AbstractRequest;
use Kimonix\Kimonix\Model\Request\Factory as RequestFactory;
use Kimonix\Kimonix\Model\Response\Factory as ResponseFactory;
use Magento\Catalog\Model\Category;

/**
 * Kimonix categories/update request model.
 */
class Update extends AbstractRequest
{
    /**
     * @var Category
     */
    protected $_category;

    /**
     * @param  Category $category
     * @return $this
     */
    public function setCategory(Category $category)
    {
        $this->_category = $category;
        return $this;
    }

    /**
     * @return Category $category
     */
    public function getCategory()
    {
        return $this->_category;
    }

    /**
     * @return string
     */
    protected function getCurlMethod()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getRequestMethod()
    {
        return RequestFactory::CATEGORIES_UPDATE_REQUEST_METHOD;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getResponseHandlerType()
    {
        return ResponseFactory::CATEGORIES_UPDATE_RESPONSE_HANDLER;
    }

    /**
     * Return request params.
     *
     * @return array
     */
    protected function getParams()
    {
        $category = $this->getCategory();
        return array_replace_recursive(
            parent::getParams(),
            [
              'id' => $category->getId(),
              'description' => $category->getDescription(),
              'url_key' => $category->getUrlKey(),
              'parent_id' => $category->getParentId(),
            ]
        );
    }
}
