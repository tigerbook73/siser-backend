<?php

namespace Tests;

use Faker\Generator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
  use CreatesApplication;
}
