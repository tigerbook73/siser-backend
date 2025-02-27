<?php

namespace Tests\Unit;

use App\Models\LicensePackagePriceTable;
use Exception;
use Tests\TestCase;

class LicensePackagePriceTableTest extends TestCase
{
  public $defaultSteps = [
    ['from' => 2, 'to' => 5, 'discount' => 10, 'base_discount' => 0],
    ['from' => 6, 'to' => 10, 'discount' => 20, 'base_discount' => 40],
    ['from' => 11, 'to' => 20, 'discount' => 30, 'base_discount' => 140],
  ];
  public $defaultRange = [
    [2, 10],
    [12, 12],
    [15, 15],
    [20, 20]
  ];
  public $defaultPriceRates = [
    ['quantity' => 2, 'price_rate' => 190],
    ['quantity' => 3, 'price_rate' => 280],
    ['quantity' => 4, 'price_rate' => 370],
    ['quantity' => 5, 'price_rate' => 460],
    ['quantity' => 6, 'price_rate' => 540],
    ['quantity' => 7, 'price_rate' => 620],
    ['quantity' => 8, 'price_rate' => 700],
    ['quantity' => 9, 'price_rate' => 780],
    ['quantity' => 10, 'price_rate' => 860],
    ['quantity' => 12, 'price_rate' => 1000],
    ['quantity' => 15, 'price_rate' => 1210],
    ['quantity' => 20, 'price_rate' => 1560],
  ];

  public function testDefaultOk()
  {
    $table = new LicensePackagePriceTable($this->defaultSteps, $this->defaultRange);

    // assert price steps count
    $this->assertCount(count($this->defaultSteps), $table->price_steps);

    // assert price steps
    for ($i = 0; $i < count($this->defaultSteps); $i++) {
      $sourceSteps = $this->defaultSteps[$i];
      $resultSteps = $table->price_steps[$i];
      $this->assertEquals($sourceSteps['from'], $resultSteps->from);
      $this->assertEquals($sourceSteps['to'], $resultSteps->to);
      $this->assertEquals($sourceSteps['discount'], $resultSteps->discount);
      $this->assertEquals($sourceSteps['base_discount'], $resultSteps->base_discount);
    }

    // assert range count
    $this->assertCount(count($this->defaultRange), $table->range);

    // assert range
    for ($i = 0; $i < count($this->defaultRange); $i++) {
      $sourceRange = $this->defaultRange[$i];
      $resultRange = $table->range[$i];
      $this->assertEquals($sourceRange[0], $resultRange[0]);
      $this->assertEquals($sourceRange[1], $resultRange[1]);
    }

    // assert price list count
    $this->assertCount(count($this->defaultPriceRates), $table->price_list);

    // assert price list
    for ($i = 0; $i < count($this->defaultPriceRates); $i++) {
      $sourcePriceRate = $this->defaultPriceRates[$i];
      $resultPriceRate = $table->price_list[$i];
      $this->assertEquals($sourcePriceRate['quantity'], $resultPriceRate->quantity);
      $this->assertEquals($sourcePriceRate['price_rate'], $resultPriceRate->price_rate);
    }
  }
}
