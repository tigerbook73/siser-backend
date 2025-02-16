<?php

namespace App\Services\Paddle;

use App\Models\Paddle\ProductCustomData;
use App\Models\PaddleMap;
use App\Models\Product;
use App\Models\ProductInterval;
use Paddle\SDK\Entities\Product as PaddleProduct;
use Paddle\SDK\Entities\Shared\CatalogType;
use Paddle\SDK\Entities\Shared\TaxCategory;
use Paddle\SDK\Resources\Products\Operations\CreateProduct;
use Paddle\SDK\Resources\Products\Operations\UpdateProduct;

class ProductService extends PaddleEntityService
{
  /**
   * Prepare CreateProduct or UpdateProduct from product
   *
   * @param Product $product
   * @param ProductInterval $interval
   * @param PaddleOperation $mode
   */
  public function prepareData(Product $product, ProductInterval $interval, PaddleOperation $mode): CreateProduct|UpdateProduct
  {
    $customData = ProductCustomData::from([
      "product_id"        => $product->id,
      "product_name"      => $product->name,
      "product_type"      => $product->type,
      "product_interval"  => $interval->value,
      "product_timestamp" => $product->updated_at?->format('Y-m-d H:i:s'),
    ])->toCustomData();

    if ($mode === PaddleOperation::CREATE) {
      return new CreateProduct(
        name: $product->name,
        taxCategory: new TaxCategory('standard'),
        type: new CatalogType('standard'),
        description: $interval->value,
        customData: $customData,
        imageUrl: "https://cdn.leonardodesignstudio.com/images/software/leonardo-design-studio-logo.svg"
      );
    } else {
      return new UpdateProduct(
        name: $product->name,
        taxCategory: new TaxCategory('standard'),
        type: new CatalogType('standard'),
        description: $interval->value,
        customData: $customData,
        imageUrl: "https://cdn.leonardodesignstudio.com/images/software/leonardo-design-studio-logo.svg"
      );
    }
  }

  /**
   * Create a new product (interval based) in Paddle
   *
   * @param Product $product
   * @param ProductInterval $interval
   * @return PaddleProduct
   */
  public function createPaddleProduct(Product $product, ProductInterval $interval): PaddleProduct
  {
    $createProduct = $this->prepareData($product, $interval, PaddleOperation::CREATE);
    $paddleProduct = $this->paddleService->createProduct($createProduct);
    $this->updateProduct($product, $paddleProduct);
    return $paddleProduct;
  }

  /**
   * Update an existing product (interval based) in Paddle
   *
   * @param Product $product
   * @param ProductInterval $interval
   * @return PaddleProduct
   */
  public function updatePaddleProduct(Product $product, ProductInterval $interval): PaddleProduct
  {
    $meta = $product->getMeta();
    if (!$meta->paddle->getProductId($interval)) {
      throw new \Exception('Paddle product not exist');
    }

    $updateProduct = $this->prepareData($product, $interval, PaddleOperation::UPDATE);
    return $this->paddleService->updateProduct($meta->paddle->getProductId($interval), $updateProduct);
  }

  /**
   * Create or update a product (interval based) in Paddle
   *
   * @param Product $product
   * @param ProductInterval $interval
   * @return PaddleProduct
   */
  public function createOrUpdatePaddleProduct(Product $product, ProductInterval $interval): PaddleProduct
  {
    if ($product->getMeta()->paddle->getProductId($interval)) {
      return $this->updatePaddleProduct($product, $interval);
    } else {
      return $this->createPaddleProduct($product, $interval);
    }
  }

  /**
   * Create or update all interval based products in Paddle
   *
   * @param Product $product
   */
  public function createOrUpdatePaddleProducts(Product $product): Product
  {
    foreach (ProductInterval::cases() as $interval) {
      $this->createOrUpdatePaddleProduct($product, $interval);
    }
    return $product;
  }

  /**
   * Update product meta data with Paddle product id
   *
   * @param Product $product
   * @param PaddleProduct $paddleProduct
   */
  public function updateProduct(Product $product, PaddleProduct $paddleProduct): Product
  {
    $interval = ProductCustomData::from($paddleProduct->customData?->data)->product_interval;
    $product->setMetaPaddleProductId($paddleProduct->id, $interval)
      ->save();
    PaddleMap::createOrUpdate($paddleProduct->id, Product::class, $product->id, $interval);
    return $product;
  }

  public function refreshPaddleProducts(Product $product): Product
  {
    foreach (
      [
        ProductInterval::INTERVAL_1_MONTH,
        ProductInterval::INTERVAL_1_YEAR,
        ProductInterval::INTERVAL_2_DAY
      ] as $interval
    ) {
      $this->createOrUpdatePaddleProduct($product, $interval);
    }
    return $product;
  }
}
