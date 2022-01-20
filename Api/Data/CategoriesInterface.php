<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Api\Data;

/**
 * Interface CategoriesInterface
 * @api
 */
interface CategoriesInterface extends BasicResponseInterface
{
    /*
     * categories
     */
    const CATEGORIES = 'categories';

    /**
     * @return \Kimonix\Kimonix\Api\Data\CategoryInterface[]|null $categories.
     */
    public function getCategories();

    /**
     * @param \Kimonix\Kimonix\Api\Data\CategoryInterface[]|null $categories.
     * @return $this
     */
    public function setCategories($categories);
}
