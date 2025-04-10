<?php

namespace App\Models;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class LicensePackagePriceTable
 *
 * This class represents a price table for a license package.
 *
 * @property LicensePackagePriceStep[] $price_steps tiered discount steps
 * @property array $range, [[2,10], [12,12], [15,15], [20,20], [30,30]]
 * @property LicensePackagePriceRate[] $price_list
 *
 * Example data in array format:
 * ```
 * [
 *  'price_steps' => [
 *    ['from' => 2,   'to' => 5,  'discount' => 10, 'base_discount' => 0],
 *    ['from' => 6,   'to' => 10, 'discount' => 20, 'base_discount' => 40],
 *    ['from' => 11,  'to' => 20, 'discount' => 30, 'base_discount' => 140],
 *  ],
 * 'range' => [[2, 10], [12, 12], [15, 15], [20, 20]],
 * 'price_list' => [
 *    // step 1, price_rate = quantity * 100 - base_discount - discount * (quantity - from + 1)
 *    ['quantity' => 2,   'price_rate' => 190], // 2 * 100 - 0 - 10 * (2 - 2 + 1) = 190
 *    ['quantity' => 3,   'price_rate' => 280], // 3 * 100 - 0 - 10 * (3 - 2 + 1) = 280
 *    ['quantity' => 4,   'price_rate' => 370], // 4 * 100 - 0 - 10 * (4 - 2 + 1) = 370
 *    ['quantity' => 5,   'price_rate' => 460], // 5 * 100 - 0 - 10 * (5 - 2 + 1) = 460
 *    // step 2
 *    ['quantity' => 6,   'price_rate' => 540], // 6 * 100 - 40 - 20 * (6 - 6 + 1) = 540
 *    ['quantity' => 7,   'price_rate' => 620], // 7 * 100 - 40 - 20 * (7 - 6 + 1) = 620
 *    ['quantity' => 8,   'price_rate' => 700], // 8 * 100 - 40 - 20 * (8 - 6 + 1) = 700
 *    ['quantity' => 9,   'price_rate' => 780], // 9 * 100 - 40 - 20 * (9 - 6 + 1) = 780
 *    ['quantity' => 10,  'price_rate' => 860], // 10 * 100 - 40 - 20 * (10 - 6 + 1) = 860
 *    // step 3
 *    ['quantity' => 12,  'price_rate' => 1000], // 12 * 100 - 140 - 30 * (12 - 11 + 1) = 1000
 *    ['quantity' => 15,  'price_rate' => 1210], // 15 * 100 - 140 - 30 * (15 - 11 + 1) = 1210
 *    ['quantity' => 20,  'price_rate' => 1560], // 20 * 100 - 140 - 30 * (20 - 11 + 1) = 1560
 * ]
 * ```
 */
class LicensePackagePriceTable implements Arrayable
{
  public array $price_steps;
  public array $range;
  public array $price_list;

  public function __construct(array $price_steps, array $range)
  {
    $this->price_steps = self::parsePriceSteps($price_steps);
    $this->range = self::parseRange($range);
    $this->refreshPriceList();
  }

  /**
   * Parse the price steps array and validate it
   *
   * @param array $price_steps
   * @return ?array<LicensePackagePriceStep>
   */
  public function parsePriceSteps(array $price_steps): ?array
  {
    if (count($price_steps) === 0) {
      throw new Exception("Price steps cannot be empty.");
    }

    // convert the price steps to objects
    $priceSteps = array_map(fn($step) => LicensePackagePriceStep::from($step), $price_steps);

    // sort the price steps by the 'to' field
    usort($priceSteps, fn($a, $b) => $a->to - $b->to);

    // validate and update the price steps
    $previousStep = $priceSteps[0];
    if ($previousStep->from !== LicensePackage::MIN_QUANTITY) {
      throw new Exception("First step must start from " . LicensePackage::MIN_QUANTITY . ".");
    }
    for ($i = 1; $i < count($priceSteps); $i++) {
      $step = $priceSteps[$i];

      // update or validate the from field
      if ($step->from === LicensePackage::MIN_QUANTITY) {
        $step->from = $previousStep->to + 1;
      } else if ($step->from !== $previousStep->to + 1) {
        throw new Exception("Price steps must be continuous.");
      }

      // validate the discount
      if ($step->discount < $previousStep->discount) {
        throw new Exception("Discounts must be non-decreasing.");
      }

      // update the base discount
      $step->base_discount = $previousStep->base_discount + ($previousStep->to - $previousStep->from + 1) * $previousStep->discount;
      $previousStep = $step;
    }
    return $priceSteps;
  }


  /**
   * Validate and normalize the range string to a standard format
   *
   * @param array $range
   * @return ?array
   */
  static public function parseRange(array $range): ?array
  {
    if (count($range) === 0) {
      throw new Exception("Range cannot be empty.");
    }

    foreach ($range as $unit) {
      if (!is_array($unit) || count($unit) !== 2 || !is_int($unit[0]) || !is_int($unit[1])) {
        throw new Exception("Each range unit must be an array of two integers.");
      }
      if ($unit[0] < LicensePackage::MIN_QUANTITY || $unit[0] > $unit[1] || $unit[1] > LicensePackage::MAX_QUANTITY) {
        throw new Exception("Each range unit must be [from, to] where 2 <= from <= to <= " . LicensePackage::MAX_QUANTITY . ".");
      }
    }

    // sort the range
    usort($range, fn($a, $b) => $a[0] - $b[0]);

    // validate the range
    for ($i = 1; $i < count($range); $i++) {
      if ($range[$i][0] <= $range[$i - 1][1]) {
        throw new Exception("Range units must not overlap.");
      }
    }

    return $range;
  }

  /**
   * Refresh the price list based on the price steps and range
   */
  public function refreshPriceList(): void
  {
    $this->price_list = [];
    foreach ($this->range as $unit) {
      for ($quantity = $unit[0]; $quantity <= $unit[1]; $quantity++) {
        foreach ($this->price_steps as $step) {
          if ($quantity >= $step->from && $quantity <= $step->to) {
            $this->price_list[] = new LicensePackagePriceRate(
              $quantity,
              $quantity * LicensePackage::RATE_FACTOR - $step->base_discount - $step->discount * ($quantity - $step->from + 1)
            );

            if (count($this->price_list) >= LicensePackage::MAX_PRICE_COUNT) {
              return;
            }
            break;
          }
        }
      }
    }
  }

  public function validate(): void
  {
    // validate the price steps
    foreach ($this->price_steps as $index => $step) {
      $step->validate();

      if ($index === 0) {
        if ($step->from !== LicensePackage::MIN_QUANTITY) {
          throw new Exception("First step must start from " . LicensePackage::MIN_QUANTITY . ".");
        }
        if ($step->base_discount !== 0) {
          throw new Exception("First step must have zero base discount.");
        }
      } else {
        $previous = $this->price_steps[$index - 1];
        if ($step->from !== $previous->to + 1) {
          throw new Exception("Price steps must not overlap.");
        }
        if ($step->discount < $previous->discount) {
          throw new Exception("Discounts must be non-decreasing.");
        }
        if ($step->base_discount !== $previous->base_discount + ($previous->to - $previous->from + 1) * $previous->discount) {
          throw new Exception("Invalid base discount.");
        }
      }
      $previous = $step;
    }

    // more to validate
    // ...
  }

  public function toArray(): array
  {
    return [
      'price_steps' => collect($this->price_steps)->toArray(),
      'range'       => $this->range,
      'price_list'  => collect($this->price_list)->toArray(),
    ];
  }

  public static function from(array $data): LicensePackagePriceTable
  {
    /**
     * for backward compatibility
     */
    if (!isset($data['price_steps']) && !isset($data['range'])) {
      $lastStep = $data[count($data) - 1];
      $data = [
        'price_steps' => $data,
        'range' => [[LicensePackage::MIN_QUANTITY, $lastStep['to'] ?? $lastStep['quantity']]],
      ];
    }

    return new LicensePackagePriceTable(
      $data['price_steps'],
      $data['range']
    );
  }

  public function hasPriceRate(int $quantity): bool
  {
    foreach ($this->price_list as $priceRate) {
      if ($priceRate->quantity === $quantity) {
        return true;
      }
    }
    return false;
  }

  public function getPriceRate(int $quantity): LicensePackagePriceRate
  {
    foreach ($this->price_list as $priceRate) {
      if ($priceRate->quantity === $quantity) {
        return $priceRate;
      }
    }
    throw new Exception("Invalid quantity: " . $quantity);
  }
}
