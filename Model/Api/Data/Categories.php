<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Api\Data;

use Kimonix\Kimonix\Api\Data\BasicResponseInterface;
use Kimonix\Kimonix\Api\Data\CategoriesInterface;

class Categories extends BasicResponse implements BasicResponseInterface, CategoriesInterface
{
    /**
     * {@inheritdoc}
     */
    public function setCategories($categories)
    {
        return $this->setData(self::CATEGORIES, $categories);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories()
    {
        return $this->getData(self::CATEGORIES);
    }
}
