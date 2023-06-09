<?php

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('subscriptions', function (Blueprint $table) {
      $table->json('status_transitions')->nullable()->comment('{ "status": "timestamps" }');
    });

    /** @var Subscription[] $subscription */
    $subscriptions = Subscription::all();
    foreach ($subscriptions as $subscription) {
      $status_transitions[Subscription::STATUS_DRAFT] = $subscription->created_at;
      if ($subscription->start_date) {
        $status_transitions[Subscription::STATUS_ACTIVE] = $subscription->start_date;
      }
      if (
        $subscription->status == Subscription::STATUS_STOPPED ||
        $subscription->status == Subscription::STATUS_FAILED
      ) {
        $status_transitions[$subscription->status] = $subscription->end_date;
      }
      $subscription->status_transitions = $status_transitions;
      $subscription->save();
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('subscriptions', function (Blueprint $table) {
      $table->dropColumn('status_transitions');
    });
  }
};
