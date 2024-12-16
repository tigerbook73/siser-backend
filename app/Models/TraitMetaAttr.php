<?php

namespace App\Models;

trait TraitMetaAttr
{
  public function getMetaAttr(string $attr, $default = null): string|array|null
  {
    return $this->meta[$attr] ?? $default;
  }

  public function setMetaAttr(string $attr, string|array|null $value): self
  {
    $meta = $this->meta ?? [];
    $meta[$attr] = $value;
    $this->meta = $meta;
    return $this;
  }
}
