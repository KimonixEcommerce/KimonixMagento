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
 * Interface CategoryInterface
 * @api
 */
interface CategoryInterface extends BasicResponseInterface
{
    const MAGENTO_ID = 'entity_id';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const PARENT_ID = 'parent_id';
    const URL_KEY = 'url_key';
    const PATH = 'path';
    const IS_ACTIVE = 'is_active';
    const POSITION = 'position';
    const LEVEL = 'level';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DEFAULT_SORT_BY = 'default_sort_by';
    const INCLUDE_IN_MENU = 'include_in_menu';
    const IS_ANCHOR = 'is_anchor';
    const CHILDREN_IDS = 'children_ids';
    const PRODUCTS = 'products';
    const KIMONIX_CONTROL = 'kimonix_control';
    const KIMONIX_DYNAMIC_FETCH = 'kimonix_dynamic_fetch';

    /**
     * @return int $id.
     */
    public function getMagentoId();

    /**
     * @param int $id
     * @return $this
     */
    public function setMagentoId($id);

    /**
     * @return string|null $name.
     */
    public function getName();

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string|null $description.
     */
    public function getDescription();

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return int|null $parentId.
     */
    public function getParentId();

    /**
     * @param int|null $parentId
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * @return string|null $urlKey.
     */
    public function getUrlKey();

    /**
     * @param string|null $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);

    /**
     * @return string|null $path.
     */
    public function getPath();

    /**
     * @param string|null $path
     * @return $this
     */
    public function setPath($path);

    /**
     * @return bool|null
     */
    public function getIsActive();

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * @return int|null
     */
    public function getPosition();

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * @return int|null
     */
    public function getLevel();

    /**
     * @param int $level
     * @return $this
     */
    public function setLevel($level);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string|null
     */
    public function getDefaultSortBy();

    /**
     * @param string $defaultSortBy
     * @return $this
     */
    public function setDefaultSortBy($defaultSortBy);

    /**
     * @return bool|null
     */
    public function getIncludeInMenu();

    /**
     * @param bool $includeInMenu
     * @return $this
     */
    public function setIncludeInMenu($includeInMenu);

    /**
     * @return bool|null $includeInMenu
     */
    public function getIsAnchor();

    /**
     * @param bool $includeInMenu
     * @return $this
     */
    public function setIsAnchor($isAnchor);

    /**
     * @return int[]|string|null
     */
    public function getChildrenIds();

    /**
     * @param int[]|string|null $childrenIds
     * @return $this
     */
    public function setChildrenIds($childrenIds);

    /**
     * @return \Kimonix\Kimonix\Api\Data\ProductInterface[]|null $products.
     */
    public function getProducts();

    /**
     * @param \Kimonix\Kimonix\Api\Data\ProductInterface[]|null $products.
     * @return $this
     */
    public function setProducts($products);

    /**
     * @return int|null $kimonixControl.
     */
    public function getKimonixControl();

    /**
     * @param int|null $kimonixControl
     * @return $this
     */
    public function setKimonixControl($kimonixControl);

    /**
     * @return int|null $dynamicFetch.
     */
    public function getDynamicFetch();

    /**
     * @param int|null $dynamicFetch
     * @return $this
     */
    public function setDynamicFetch($dynamicFetch);
}
