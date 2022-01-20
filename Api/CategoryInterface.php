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

interface CategoryInterface
{
    /**
     * @param int $id
     * @return Data\CategoryInterface.
     */
    public function getProducts($id);

    /**
     * @param int $id
     * @param int[] $product_ids
     * @return Data\BasicResponseInterface
     */
    public function postProducts($id, $product_ids = []);

    /**
     * @param int $id
     * @param string $kimonixControl
     * @param bool|int|null $dynamicFetch
     * @return Data\BasicResponseInterface
     */
    public function setKimonixAttributes($id, $kimonixControl, $dynamicFetch = null);
}
