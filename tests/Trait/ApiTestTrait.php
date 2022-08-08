<?php

namespace Tests\Trait;

trait ApiTestTrait
{
  /**
   * Create dummy string for testing field's maxlength.
   */
  public function createRandomString(int $strLength): string
  {
    return $this->faker->regexify('[A-Za-z0-9$&+,:;=?@#|<>.^*()%!-]{' . $strLength . '}');
  }
}
