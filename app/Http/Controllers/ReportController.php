<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\StatisticRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
  const LIST_MODE_SINGLE        = 'single';
  const LIST_MODE_MONTH         = 'month';
  const LIST_MODE_DAY           = 'day';

  public function subscriptions(Request $request, $id)
  {
    abort(400, 'Not implemented');
  }

  public function summary()
  {
    $userCount = User::count();
    $machineCount = Machine::count();
    $level1Count = User::where('subscription_level', 1)->count();
    $level2Count = User::where('subscription_level', 2)->count();
    $licensedCount = $level1Count + $level2Count;

    $summary = [
      [
        'name'  => 'user.total_count',
        'title' => 'Total Users',
        'value' => $userCount,
      ],
      [
        'name'  => 'user.licensed_count',
        'title' => 'Licensed Users',
        'value' => $licensedCount,
      ],
      [
        'name'  => 'machine.total_count',
        'title' => 'Total Machines',
        'value' => $machineCount,
      ],
      [
        'name'  => 'user.level_1_count',
        'title' => 'Basic User',
        'value' => $level1Count,
      ],
      [
        'name'  => 'user.level_2_count',
        'title' => 'Pro User',
        'value' => $level2Count,
      ],
    ];

    return response()->json($summary);
  }

  public function listStaticsRecord(Request $request)
  {
    $mode = $request->mode ?? self::LIST_MODE_SINGLE;

    if ($mode == self::LIST_MODE_SINGLE) {
      return $this->listStaticsRecordSingle($request);
    }

    if ($mode == self::LIST_MODE_MONTH) {
      return $this->listStaticsRecordMonth($request);
    }

    if ($mode == self::LIST_MODE_DAY) {
      return $this->listStaticsRecordDay($request);
    }

    return [];
  }

  public function listStaticsRecordMonth(Request $request)
  {
    $limit = $request->limit ?? 24;
    $limit = $limit > 24 ? 24 : $limit;

    /** @var Carbon $first_date $last_date */
    $first_date = StatisticRecord::orderBy('date')->first()->date;
    /** @var Carbon $last_date */
    $last_date = StatisticRecord::orderBy('date', 'desc')->first()->date;

    $start_date = Carbon::parse($request->start_date ?? '2022-10-17');
    $start_date = $start_date->greaterThan($first_date) ? $start_date : Carbon::parse($first_date);

    $end_date = Carbon::parse($request->end_date ?? now());
    $end_date = $end_date->lessThan($last_date) ? $end_date : Carbon::parse($last_date);


    // month
    $start_date->startOfMonth();
    $end_date->startOfMonth();

    $dates = [];
    for ($date = $start_date->clone(); $date->lte($end_date); $date->addMonth()) {
      $endOfMonth = $date->clone()->endOfMonth();
      $dates[] = ($endOfMonth->gt($last_date) ? $last_date : $endOfMonth)->toDateString();
    }

    return empty($dates) ?
      [] : StatisticRecord::whereIn('date', $dates)->orderBy('date', 'desc')->limit($limit)->get();
  }

  public function listStaticsRecordDay(Request $request)
  {
    $limit = $request->limit ?? 60;
    $limit = $limit > 180 ? 180 : $limit;

    $interval = $request->interval ?? 1;
    $interval = $interval > 180 ? 180 : $interval;

    /** @var Carbon $first_date $last_date */
    $first_date = StatisticRecord::orderBy('date')->first()->date;
    /** @var Carbon $last_date */
    $last_date = StatisticRecord::orderBy('date', 'desc')->first()->date;

    $start_date = Carbon::parse($request->start_date ?? '2022-10-17');
    $start_date = $start_date->greaterThan($first_date) ? $start_date : Carbon::parse($first_date);

    $end_date = Carbon::parse($request->end_date ?? now());
    $end_date = $end_date->lessThan($last_date) ? $end_date : Carbon::parse($last_date);


    $dates = [];
    for ($date = $start_date->clone(); $date->lt($end_date); $date->addDays($interval)) {
      $dateTemp = $date->clone();
      $dates[] = ($dateTemp->gt($last_date) ? $last_date : $dateTemp)->toDateString();
    }
    $dates[] = $end_date->toDateString();

    return StatisticRecord::whereIn('date', $dates)->orderBy('date', 'desc')->limit($limit)->get();
  }

  public function listStaticsRecordSingle(Request $request)
  {
    $date = $request->date ? Carbon::parse($request->date)->toDateString() : null;
    if ($date) {
      return StatisticRecord::where('date', $date)->limit(1)->get();
    }

    return StatisticRecord::orderBy('date', 'desc')->limit(1)->get();
  }
}
