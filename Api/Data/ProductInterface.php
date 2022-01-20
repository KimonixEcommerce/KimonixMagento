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
 * Interface ProductInterface
 * @api
 */
interface ProductInterface extends BasicResponseInterface
{
    const ID = 'entity_id';
    const TITLE = 'name';
    const IS_ACTIVE = 'is_active';

    /**
     * @return int $id.
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string|null $title.
     */
    public function getTitle();

    /**
     * @return bool|null
     */
    public function getIsActive();

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);
}
