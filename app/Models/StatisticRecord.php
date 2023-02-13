<?php

namespace App\Models;

use App\Models\Base\StatisticRecord as BaseStatisticRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticRecord extends BaseStatisticRecord
{
  static public function generateRecords()
  {
    if ($lastRecord = StatisticRecord::orderBy('date', 'desc')->first()) {
      $startDate = $lastRecord->date->addDay();
    } else {
      $startDate = new Carbon('2022-10-17');
    }
    $endDate   = Carbon::yesterday();

    // generate records
    $records = [];
    for ($date = $startDate; $date <= $endDate; $date = $date->addDay()) {
      $record = [];
      $record['user'] = User::whereDate('created_at', '<=', $date)->count();
      $record['machine'] = Machine::whereDate('created_at', '<=', $date)->count();
      $record['licensed_user'] = Machine::whereDate('created_at', '<=', $date)  // @phpstan-ignore-line
        ->selectRaw('count(distinct user_id) as count')
        ->first()
        ->count;
      $record['licensed_user_1'] = $record['licensed_user'];

      $records[] = [
        'date'   => $date->toDateString(),
        'record' => json_encode($record),
      ];
    }
    if (count($records) > 0) {
      DB::table('statistic_records')->insert($records);
    }
  }
}
