<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\StatisticRecord;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
  public function subscriptions(Request $request, $id)
  {
    abort(400, 'Not implemented');
  }

  public function summary(Request $request)
  {
    $summary = [
      [
        'name'  => 'user.total_count',
        'title' => 'Total Users',
        'value' => User::count(),
      ],
      [
        'name'  => 'user.licensed_count',
        'title' => 'Licensed Users',
        'value' => User::where('license_count', '>', 0)->count(),
      ],
      [
        'name'  => 'machine.total_count',
        'title' => 'Total Machines',
        'value' => Machine::count(),
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
