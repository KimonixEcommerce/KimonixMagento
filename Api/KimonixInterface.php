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

interface KimonixInterface
{
    /**
     * @param  string|null $entityType
     * @return \Kimonix\Kimonix\Api\Data\BasicResponseInterface
     */
    public function resetSyncFlags($entityType = null);

    /**
     * @param  string|null $apiUrl
     * @return \Kimonix\Kimonix\Api\Data\BasicResponseInterface
     */
    public function setKimonixApiUrl($apiUrl = null);
}
