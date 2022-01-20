<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model\Api;

class Authorize extends AbstractApi implements \Kimonix\Kimonix\Api\AuthorizeInterface
{
    /**
     * {@inheritdoc}
     */
    public function authorize()
    {
        try {
            $this->authGuard();
            $this->setResponseMessage("successfully_authorized");
        } catch (\Exception $e) {
            $this->setResponseException($e);
        }

        return $this->getResponse();
    }
}
