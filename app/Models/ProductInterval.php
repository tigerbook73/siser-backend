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
      '2_day'    => ['unit' => 'day',   'count' => 1],
      '1_month'  => ['unit' => 'month', 'count' => 2],
      '1_year'   => ['unit' => 'year',  'count' => 1],
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
}
