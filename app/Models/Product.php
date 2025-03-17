<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;
use Illuminate\Database\Eloquent\Collection;

class Product extends BaseProduct
{
  use TraitMetaAttr;

  const TYPE_BASIC            = 'basic';
  const TYPE_SUBSCRIPTION     = 'subscription';

  public function getMeta(): ProductMeta
  {
    return ProductMeta::from($this->meta ?? []);
  }

  public function setMeta(ProductMeta $meta): self
  {
    $this->meta = $meta->toArray();
    return $this;
  }

  public function setMetaPaddleProductId(?string $paddleProductId, ProductInterval $interval): self
  {
    $meta = $this->getMeta();
    if ($meta->paddle->getProductId($interval) !== $paddleProductId) {
      $meta->paddle->setProductId($interval, $paddleProductId);
      return $this->setMeta($meta);
    }
    return $this;
  }

  public function setMetaPaddleProductMonthId(?string $paddleProductId): self
  {
    return $this->setMetaPaddleProductId($paddleProductId, ProductInterval::INTERVAL_1_MONTH);
  }

  public function setMetaPaddleProductYearId(?string $paddleProductId): self
  {
    return $this->setMetaPaddleProductId($paddleProductId, ProductInterval::INTERVAL_1_YEAR);
  }

  public function setMetaPaddleProduct2DayId(?string $paddleProductId): self
  {
    return $this->setMetaPaddleProductId($paddleProductId, ProductInterval::INTERVAL_2_DAY);
  }

  /**
   * @param string[]|string $type
   * @return Collection<int, Product>
   */
  static public function listProducts($type = self::TYPE_SUBSCRIPTION): Collection
  {
    if (!is_array($type)) {
      return self::where('type', $type)->get();
    } else {
      return self::whereIn('type', $type)->get();
    }
  }
}
