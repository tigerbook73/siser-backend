<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\StatisticRecord;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
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
    StatisticRecord::generateRecords();
    $limit = $request->limit > 100 ? 100 : $request->limit;
    return StatisticRecord::orderBy('date', 'desc')->limit($limit)->get();
  }
}
