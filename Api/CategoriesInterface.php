<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Api;

interface CategoriesInterface
{

    /**
     * @param  int|null   $limit
     * @param  int|null   $page
     * @return \Kimonix\Kimonix\Api\Data\CategoriesInterface
     */
    public function getCategories($limit = null, $page = null);

    /**
     * @param  \Kimonix\Kimonix\Api\Data\CategoryInterface[] $categories
     * @return \Kimonix\Kimonix\Api\Data\CategoriesInterface
     */
    public function postCategories($categories = []);
}
