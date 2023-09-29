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
      $record['licensed_user_1'] = User::where('subscription_level', 1)->whereDate('created_at', '<=', $date)->count();
      $record['licensed_user_2'] = Subscription::where('status', 'active')
        ->where('subscription_level', 2)
        ->whereDate('created_at', '<=', $date)->count();
      $record['licensed_user'] = $record['licensed_user_1'] + $record['licensed_user_2'];

      $records[] = [
        'date'   => $date->toDateString(),
        'record' => json_encode($record),
      ];
    }
    if (count($records) > 0) {
      DB::table('statistic_records')->insert($records);
    }
  }

  /**
   * This is one time jobs to calculate licensed_user_2
   */
  static public function prepareLicensedUser2()
  {
    foreach (StatisticRecord::all() as $statisticRecord) {
      $record = $statisticRecord->record;
      $record['licensed_user_2'] = Subscription::where('status', 'active')
        ->where('subscription_level', 2)
        ->whereDate('created_at', '<=', $statisticRecord->date)->count();
      $statisticRecord->record = $record;
      $statisticRecord->save();
    }
  }
}
