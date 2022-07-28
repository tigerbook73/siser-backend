<?php

namespace Tests;

use Faker\Generator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
  use CreatesApplication;

  /**
   * faker helper
   */
  public ?Generator $faker = null;

  protected function setup(): void
  {
    parent::setup();

    if (!$this->faker) {
      $this->faker = app()->make(Generator::class);
    }
  }
}
