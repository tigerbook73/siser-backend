<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class TestController extends Controller
{
  public function resetData()
  {
    Artisan::call('db:seed', ['--force' => true]);

    return response()->json(['message' => 'test data reset successfully!']);
  }
}
