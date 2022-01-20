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
 * Kimonix orders/delete request model.
 */
class Delete extends AbstractRequest
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
        return RequestFactory::CATEGORIES_DELETE_REQUEST_METHOD;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function getResponseHandlerType()
    {
        return ResponseFactory::CATEGORIES_DELETE_RESPONSE_HANDLER;
    }

    /**
     * Return request params.
     *
     * @return array
     */
    protected function getParams()
    {
        return array_replace_recursive(
            parent::getParams(),
            [
              'id' => $this->getCategory()->getId()
            ]
        );
    }
}
