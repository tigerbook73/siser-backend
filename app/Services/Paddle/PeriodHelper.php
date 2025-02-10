<?php

namespace App\Services\Paddle;

use DateTimeInterface;
use Illuminate\Support\Carbon;

class PeriodHelper
{
  static public function calcCurrentPeriod(
    string $interval,
    int $intervalCount,
    string|DateTimeInterface $startDate,
    string|DateTimeInterface $periodStartDate,
  ) {
    $startDate = Carbon::parse($startDate);
    $periodStartDate = Carbon::parse($periodStartDate);
    $intervalCount = intval($intervalCount);

    if ($interval == 'day') {
      $offset = $periodStartDate->startOfDay()->diffInDays($startDate->startOfDay()) / $intervalCount;
    } elseif ($interval == 'month') {
      $offset = $periodStartDate->startOfMonth()->diffInMonths($startDate->startOfMonth()) / $intervalCount;
    } elseif ($interval == 'year') {
      $offset = $periodStartDate->startOfYear()->diffInYears($startDate->startOfYear()) / $intervalCount;
    } else {
      throw new \Exception('Invalid interval');
    }

    return $offset + 1;
  }
}
