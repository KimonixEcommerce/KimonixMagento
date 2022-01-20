<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Model;

use Kimonix\Kimonix\Model\Config as KimonixConfig;
use Magento\Catalog\Model\Product\Image\UrlBuilder as ProductUrlBuilder;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductTypeConfigurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped as ProductTypeGrouped;
use Magento\Bundle\Model\Product\Type as ProductTypeBundle;
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Kimonix schema.
 */
class Schema
{
    /**
     * @var null|string
     */
    private $productDefaultPlaceholderUrl;

    /**
     * @var ProductUrlBuilder
     */
    private $productUrlBuilder;

    /**
     * @var StockItemRepository
     */
    private $stockItemRepository;

    /**
     * @var ProductStatus
     */
    private $productStatus;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @method __construct
     * @param  ProductUrlBuilder   $productUrlBuilder
     * @param  StockItemRepository $stockItemRepository
     * @param  ProductStatus       $productStatus
     * @param  TimezoneInterface   $localeDate
     */
    public function __construct(
        ProductUrlBuilder $productUrlBuilder,
        StockItemRepository $stockItemRepository,
        ProductStatus $productStatus,
        TimezoneInterface $localeDate
    ) {
        $this->productUrlBuilder = $productUrlBuilder;
        $this->stockItemRepository = $stockItemRepository;
        $this->productStatus = $productStatus;
        $this->localeDate = $localeDate;
    }

    /**
     * @method formatDate
     * @param  string     $date
     * @param  string     $format
     * @return string
     */
    public function formatDate($date, $format = KimonixConfig::KIMONIX_DATE_FORMAT)
    {
        return \date($format, strtotime($date));
    }

    /**
     * @method getVisibleStatusIds
     * @return int[]
     */
    public function getVisibleStatusIds()
    {
        return $this->productStatus->getVisibleStatusIds();
    }

    /**
     * getProductDefaultPlaceholderUrl
     * @param null|string $placeholder
     * @return string
     */
    public function getProductDefaultPlaceholderUrl($placeholder = null)
    {
        if ($this->productDefaultPlaceholderUrl === null) {
            $this->productDefaultPlaceholderUrl = $this->catalogImageHelper->getDefaultPlaceholderUrl($placeholder);
        }
        return $this->productDefaultPlaceholderUrl;
    }

    /**
     * @method getProductImageUrl
     * @param  string            $baseFilePath
     * @param  string            $imageDisplayArea
     * @param  string            $fallbackFilePath
     * @return string
     */
    public function getProductImageUrl($baseFilePath, $imageDisplayArea = 'product_page_image_large', $fallbackFilePath = null)
    {
        if (!$baseFilePath || $baseFilePath === 'no_selection') {
            if ($fallbackFilePath && $fallbackFilePath !== 'no_selection') {
                $baseFilePath = $fallbackFilePath;
            } else {
                return null;
            }
        }
        return $this->productUrlBuilder->getUrl($baseFilePath, $imageDisplayArea) ?: null;
    }

    /**
     * @method getProductStockItem
     * @param  Product             $product
     * @return object|null
     */
    public function getProductStockItem(Product $product)
    {
        return $product->getStockItem() ?: $this->stockItemRepository->get($product->getId());
    }

    /**
     * @method isProductNew
     * @param  Product      $product
     * @return boolean
     */
    public function isProductNew(Product $product)
    {
        $newsFromDate = $product->getNewsFromDate();
        $newsToDate = $product->getNewsToDate();
        if (!$newsFromDate && !$newsToDate) {
            return false;
        }
        return $this->localeDate->isScopeDateInInterval(
            $product->getStore(),
            $newsFromDate,
            $newsToDate
        );
    }

    /**
     * @method getProductSchema
     * @param  Product            $product
     * @return array
     */
    public function getProductSchema(Product $product)
    {
        $schema = [
            "id" => (int) $product->getId(),
            "sku" => (string) $product->getSku(),
            "created_at" => (string) $this->formatDate($product->getCreatedAt()),
            "updated_at" => (string) $this->formatDate($product->getUpdatedAt()),
            "body_html" => (string) $product->getShortDescription(),
            "title" => (string) $product->getName(),
            "is_active" => (bool) in_array($product->getStatus(), $this->getVisibleStatusIds()),
            "url_key" => (string) $product->getUrlKey(),
            "url" => (string) $product->getProductUrl(),
            "type_id" => (string) $product->getTypeId(),
            "cost" => (float) $product->getCost(),
            "regular_price" => (float) $product->getPriceInfo()->getPrice('regular_price')->getValue(),
            "final_price" => (float) $product->getFinalPrice(),
            "image" => [
                "src" => $this->getProductImageUrl($product->getImage(), 'product_page_image_large', $product->getSmallImage()),
            ],
            "thumbnail" => [
                "src" => $this->getProductImageUrl($product->getThumbnail(), 'product_page_image_small', $product->getImage()),
            ],
            "is_new" => $this->isProductNew($product),
            "is_update" => $product->getKimonixSyncFlag() === null ? false : true,
        ];

        if (($stockItem = $this->getProductStockItem($product))) {
            $schema["inventory"] = (int) $stockItem->getQty();
            $schema["inventory_tracked"] = (bool) $stockItem->getManageStock();
            $schema["is_in_stock"] = (bool) $stockItem->getIsInStock();
        }

        $childKey = false;
        if ($product->getTypeId() == ProductType::TYPE_SIMPLE) {
            $childProducts = [$product];
            $childKey = "variants";
        } elseif ($product->getTypeId() == ProductTypeConfigurable::TYPE_CODE) {
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            $childKey = "variants";
        } elseif (($typeInstance = $product->getTypeInstance()) &&
            (($childProducts = $typeInstance->getAssociatedProducts($product)))
        ) {
            $childProducts = $product->getTypeInstance()->getAssociatedProducts($product);
            $childKey = "variants";
        }

        if ($childKey && $childProducts) {
            $schema[$childKey] = [];

            foreach ($childProducts as $childProduct) {
                $childSchema = [
                    "id" => (int) $childProduct->getId(),
                    "sku" => (string) $childProduct->getSku(),
                    "title" => (string) $childProduct->getName(),
                    "is_active" => (bool) in_array($childProduct->getStatus(), $this->getVisibleStatusIds()),
                    "regular_price" => (float) $childProduct->getPriceInfo()->getPrice('regular_price')->getValue(),
                    "final_price" => (float) $childProduct->getFinalPrice(),
                    "cost" => (float) $childProduct->getCost(),
                    "inventory_item" => [
                        "cost" => (float) $childProduct->getCost(),
                    ],
                    "type_id" => (string) $childProduct->getTypeId(),
                    "image" => [
                        "src" => $this->getProductImageUrl($childProduct->getImage(), 'product_page_image_large', $childProduct->getSmallImage()),
                    ],
                    "thumbnail" => [
                        "src" => $this->getProductImageUrl($childProduct->getThumbnail(), 'product_page_image_small', $childProduct->getImage()),
                    ],
                ];

                if (($stockItem = $this->getProductStockItem($childProduct))) {
                    $childSchema["inventory_quantity"] = (int) $stockItem->getQty();
                    $childSchema["inventory_item"]["tracked"] = (bool) $stockItem->getManageStock();
                    $childSchema["inventory_item"]["is_in_stock"] = (bool) $stockItem->getIsInStock();
                }
                $schema[$childKey][] = $childSchema;
            }

            $schema["cost"] = $schema["cost"] ?: min(array_column($schema[$childKey], "cost"));
            $schema["regular_price"] = $schema["regular_price"] ?: min(array_column($schema[$childKey], "regular_price"));
            $schema["final_price"] = $schema["final_price"] ?: min(array_column($schema[$childKey], "final_price"));
            $schema["inventory"] = array_sum(array_column($schema[$childKey], "inventory_quantity"));
        }

        if (($addsToCart = $product->getData('adds_to_cart')) !== null) {
            $schema["adds_to_cart"] = (int) $product->getData('adds_to_cart');
        }

        $schema["num_reviews"] = (int) $product->getData('num_reviews');
        $schema["avg_rating"] = (int) $product->getData('avg_rating');

        return $schema;
    }

    /**
     * @method getOrderSchema
     * @param  Order            $product
     * @return array
     */
    public function getOrderSchema(Order $order)
    {
        $schema = [
            "id" => (int) $order->getId(),
            "increment_id" => (string) $order->getIncrementId(),
            "created_at" => (string) $this->formatDate($order->getCreatedAt()),
            "updated_at" => (string) $this->formatDate($order->getUpdatedAt()),
            "customer" => [
                (int) "id" => $order->getCustomerId(),
                (string) "email" => $order->getCustomerEmail()
            ],
            "lineItems" => $this->getLineItemsSchema($order),
            "currency" => (string) $order->getBaseCurrencyCode(),
            "shippingLine" => [
                "price" => (float) $order->getBaseShippingAmount()
            ],
            "totalPrice" => (float) $order->getBaseTaxAmount(),
            "totalTax" => (float) $order->getBaseGrandTotal(),
            "is_update" => $order->getKimonixSyncFlag() === null ? true : false,
        ];

        if (!$schema["lineItems"]) {
            throw new \Exception("No Line Items [SKIPPING]");
        }

        if (($billing = $order->getBillingAddress()) !== null) {
            if (!$schema['customer']['email']) {
                $schema['customer']['email'] = (string) $billing->getEmail();
            }
            $schema['customer']['phone'] = (string) $billing->getTelephone();
        }

        return $schema;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getLineItemsSchema(Order $order)
    {
        $schema = [];

        foreach ($order->getAllVisibleItems() as $orderItem) {
            $schema[] = [
                "product" => [
                    "id" => (int) $orderItem->getProductId(),
                ],
                "discountedTotalSet" => [
                    "shopMoney" => [
                        "amount" => (float) $orderItem->getBaseDiscountAmount()
                    ],
                ],
                "quantity" => (int) $orderItem->getQtyOrdered(),
            ];
        }

        return $schema;
    }
}
