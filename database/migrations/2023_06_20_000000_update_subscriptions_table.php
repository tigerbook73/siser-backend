<?php

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
      $table->foreignId('active_invoice_id')->nullable();
      $table->json('status_transitions')->nullable()->comment('{ "status": "timestamps" }');

      $table->dropColumn('processing_fee_info');
      $table->dropColumn('processing_fee');
      $table->json('next_invoice')->nullable()->comment(json_encode(json_decode('{
        "plan_info": {},
        "coupon_info": {},
        "price": 10.0,
        "subtotal": 10.2,
        "tax_rate": 0.1,
        "total_tax": 1.02,
        "total_amount": 11.22,
        "current_period_start_date": "date-time",
        "current_period_end_date": "date-time"
      }')))->change();
    });

    /** @var User $user2test */
    $user2test = User::where('name', 'user2.test')->first();
    if ($user2test) {
      $user2test->subscriptions()
        ->where('status', Subscription::STATUS_STOPPED)
        ->delete();
    }

    Subscription::chunk(500, function ($subscriptions) {
      $subscriptionRecords = [];

      /** @var Subscription[] $subscriptions */
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

        if ($subscription->isDirty()) {
          $subscription->updated_at = now();
          $subscriptionRecords[] = $subscription->getAttributes();
        }
      }
      Subscription::upsert($subscriptionRecords, ['id']);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
  }
};
