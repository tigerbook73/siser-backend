<?php

namespace App\Models;

trait TraitDrAttr
{
  public function getDrAttr(string $attr): string|array|null
  {
    return $this->dr[$attr] ?? null;
  }

  public function setDrAttr(string $attr, string|array|null $value)
  {
    $dr = $this->dr ?? [];
    $dr[$attr] = $value;
    $this->dr = $dr;
    return $this;
  }
}
