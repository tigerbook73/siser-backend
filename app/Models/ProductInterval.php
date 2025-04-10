<?php

namespace App\Models;

enum ProductInterval: string
{
  case INTERVAL_2_DAY   = '2_day';
  case INTERVAL_1_MONTH = '1_month';
  case INTERVAL_1_YEAR  = '1_year';

  /**
   * return array of intervals
   *
   * @return array
   */
  static public function intervals(): array
  {
    return [
      self::INTERVAL_2_DAY->value    => ['unit' => 'day',   'count' => 2, 'friendly_name' => '2-Day'],
      self::INTERVAL_1_MONTH->value  => ['unit' => 'month', 'count' => 1, 'friendly_name' => 'Monthly'],
      self::INTERVAL_1_YEAR->value   => ['unit' => 'year',  'count' => 1, 'friendly_name' => 'Annual'],
    ];
  }

  /**
   * build ProductInterval from unit and count
   */
  static public function build(string $unit, int $count): ?self
  {
    return self::tryFrom($count . '_' . $unit);
  }

  /**
   * check if ProductInterval exists
   */
  static public function exists(string $unit, $count): bool
  {
    return self::build($unit, $count) !== null;
  }

  /**
   * get default count for interval unit
   *
   * @param string $unit
   * @return int default is 1
   */
  static public function getDefaultIntervalCount(string $unit): int
  {
    // find the first interval with the same unit
    $interval = collect(self::intervals())->first(fn($interval) => $interval['unit'] === $unit);
    return $interval['count'] ?? 1;
  }

  public function getUnit(): string
  {
    return self::intervals()[$this->value]['unit'];
  }

  public function getCount(): int
  {
    return self::intervals()[$this->value]['count'];
  }

  public function getFriendlyName(): string
  {
    return self::intervals()[$this->value]['friendly_name'];
  }
}
