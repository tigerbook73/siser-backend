<?php

namespace App\Services\Paddle;

use App\Models\Paddle\ProductCustomData;
use App\Models\PaddleMap;
use App\Models\Product;
use Paddle\SDK\Entities\Product as PaddleProduct;
use Paddle\SDK\Entities\Shared\CatalogType;
use Paddle\SDK\Entities\Shared\TaxCategory;
use Paddle\SDK\Notifications\Entities\Product as NotificationProduct;
use Paddle\SDK\Resources\Products\Operations\CreateProduct;
use Paddle\SDK\Resources\Products\Operations\UpdateProduct;

class ProductService extends PaddleEntityService
{
  public function preparePaddleProduct(Product $product, string $mode): CreateProduct|UpdateProduct
  {
    if ($mode !== 'create' && $mode !== 'update') {
      throw new \Exception('Invalid mode');
    }

    $customData = ProductCustomData::from([
      "product_id"        => $product->id,
      "product_name"      => $product->name,
      "product_type"      => $product->type,
      "product_timestamp" => $product->updated_at?->format('Y-m-d H:i:s'),
    ])->toCustomData();

    if ($mode == 'create') {
      return new CreateProduct(
        name: $product->name,
        taxCategory: new TaxCategory('standard'),
        type: new CatalogType('standard'),
        description: $product->name,
        customData: $customData,
        imageUrl: "https://cdn.leonardodesignstudio.com/images/software/leonardo-design-studio-logo.svg"
      );
    } else {
      return new UpdateProduct(
        name: $product->name,
        taxCategory: new TaxCategory('standard'),
        type: new CatalogType('standard'),
        description: $product->name,
        customData: $customData,
      );
    }
  }

  public function createPaddleProduct(Product $product): PaddleProduct
  {
    $createProduct = $this->preparePaddleProduct($product, 'create');
    $paddleProduct = $this->paddleService->createProduct($createProduct);
    $this->updateProduct($product, $paddleProduct);
    return $paddleProduct;
  }

  public function updatePaddleProduct(Product $product): PaddleProduct
  {
    $meta = $product->getMeta();
    if (!$meta->paddle->product_id) {
      throw new \Exception('Paddle product not exist');
    }

    $updateProduct = $this->preparePaddleProduct($product, 'update');
    return $this->paddleService->updateProduct($meta->paddle->product_id, $updateProduct);
  }

  public function createOrUpdatePaddleProduct(Product $product): PaddleProduct
  {
    if ($product->getMeta()->paddle->product_id) {
      return $this->updatePaddleProduct($product);
    } else {
      return $this->createPaddleProduct($product);
    }
  }

  public function updateProduct(Product $product, PaddleProduct|NotificationProduct $paddleProduct): Product
  {
    $product->setMetaPaddleProductId($paddleProduct->id)
      ->save();

    PaddleMap::createOrUpdate($paddleProduct->id, Product::class, $product->id);
    return $product;
  }
}
