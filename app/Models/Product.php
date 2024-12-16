<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;

class Product extends BaseProduct
{
  use TraitMetaAttr;

  const TYPE_BASIC            = 'basic';
  const TYPE_SUBSCRIPTION     = 'subscription';
  const TYPE_LICENSE_PACKAGE  = 'license-package';

  public function getMeta(): ProductMeta
  {
    return ProductMeta::from($this->meta);
  }

  public function setMeta(ProductMeta $meta): self
  {
    $this->meta = $meta->toArray();
    return $this;
  }

  public function setMetaPaddleProductId(?string $paddleProductId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->product_id = $paddleProductId;
    return $this->setMeta($meta);
  }
}
