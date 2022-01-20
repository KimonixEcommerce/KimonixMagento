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

use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Kimonix\Kimonix\Api\Data\BasicResponseInterface;
use Kimonix\Kimonix\Api\Data\CategoryInterface;

class Category extends BasicResponse implements BasicResponseInterface, CategoryInterface
{

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = self::MAGENTO_ID;

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::MAGENTO_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setMagentoId($id)
    {
        return $this->setData(self::MAGENTO_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityId($id)
    {
        return $this->setData(self::MAGENTO_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setUrlKey($name)
    {
        return $this->setData(self::URL_KEY, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($name)
    {
        return $this->setData(self::PATH, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setLevel($level)
    {
        return $this->setData(self::LEVEL, $level);
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel()
    {
        return $this->getData(self::LEVEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT) === null ? null : \date(KimonixConfig::KIMONIX_DATE_FORMAT, strtotime($this->getData(self::CREATED_AT)));
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT) === null ? null : \date(KimonixConfig::KIMONIX_DATE_FORMAT, strtotime($this->getData(self::UPDATED_AT)));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSortBy($defaultSortBy)
    {
        return $this->setData(self::DEFAULT_SORT_BY, $defaultSortBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSortBy()
    {
        return $this->getData(self::DEFAULT_SORT_BY);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncludeInMenu($includeInMenu)
    {
        return $this->setData(self::INCLUDE_IN_MENU, $includeInMenu);
    }

    /**
     * {@inheritdoc}
     */
    public function getIncludeInMenu()
    {
        return $this->getData(self::INCLUDE_IN_MENU);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAnchor($isAnchor)
    {
        return $this->setData(self::IS_ANCHOR, $isAnchor);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAnchor()
    {
        return $this->getData(self::IS_ANCHOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setChildrenIds($childrenIds)
    {
        return $this->setData(self::CHILDREN_IDS, $childrenIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenIds()
    {
        return $this->getData(self::CHILDREN_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setProducts($products)
    {
        return $this->setData(self::PRODUCTS, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts()
    {
        return $this->getData(self::PRODUCTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setKimonixControl($kimonixControl)
    {
        return $this->setData(self::KIMONIX_CONTROL, $kimonixControl);
    }

    /**
     * {@inheritdoc}
     */
    public function getKimonixControl()
    {
        return $this->getData(self::KIMONIX_CONTROL);
    }

    /**
     * {@inheritdoc}
     */
    public function setDynamicFetch($dynamicFetch)
    {
        return $this->setData(self::KIMONIX_DYNAMIC_FETCH, $dynamicFetch);
    }

    /**
     * {@inheritdoc}
     */
    public function getDynamicFetch()
    {
        return $this->getData(self::KIMONIX_DYNAMIC_FETCH);
    }
}
