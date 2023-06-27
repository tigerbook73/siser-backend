<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateVersionOne extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'siser:migrate-version-one';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Fix the migrations for 20230331-20230612';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    // check state
    $lastMigration = DB::table('migrations')->orderByDesc('id')->first();
    if (
      $lastMigration->id != 48 ||
      $lastMigration->migration != '2023_06_12_000000_create_lds_licenses_table' ||
      $lastMigration->batch != 8
    ) {
      return self::SUCCESS;
    }

    $this->info('Fixed migrations between 20230331-20230612 ... begin');

    // remove migration 26-48
    $this->info('Remove migration 26-48');
    DB::table('migrations')->where('id', '>=', 26)->delete();


    // 2023_06_12_000000_create_lds_licenses_table
    $this->info('fix lds_licenses_table');
    Schema::dropIfExists('lds_licenses');

    // 2023_06_11_000000_update_users_table
    // 2023_05_16_000000_update_users_table
    // 2023_03_31_000000_update_users_table
    $this->info('fix users_table');
    Schema::table('users', function ($table) {
      $table->dropColumn('type');
      $table->dropColumn('dr');
    });

    // 2023_06_09_000001_update_invoices_table
    // 2023_06_08_000000_update_invoices_table
    // 2023_05_31_000000_update_invoices_table
    // 2023_03_31_000000_create_invoices_table
    $this->info('fix invoices_table');
    schema::dropIfExists('invoices');

    // 2023_06_09_000000_update_payment_method_table
    // 2023_03_31_000000_create_payment_methods_table
    $this->info('fix payment_methods_table');
    schema::dropIfExists('payment_methods');

    // 2023_06_08_000000_update_subscriptions_table
    // 2023_05_19_000000_update_subscriptions_table
    // 2023_03_31_000000_update_subscriptions_table
    $this->info('fix subscriptions_table');
    Schema::table('subscriptions', function ($table) {
      // 2023_06_08_000000_update_subscriptions_table
      $table->dropColumn('status_transitions');

      // 2023_05_19_000000_update_subscriptions_table
      $table->dropColumn('active_invoice_id');

      // 2023_03_31_000000_update_subscriptions_table
      $table->dropIndex(['subscription_level']);
      $table->dropIndex(['current_period']);
      if (Schema::hasColumn('subscriptions', 'dr_subscription_id')) {
        $table->dropIndex(['dr_subscription_id']); // NEW
      }
      $table->dropIndex(['status']);
      $table->dropIndex(['sub_status']);

      $table->dropForeign(['coupon_id']);

      $table->float('price')->change();
      $table->datetime('start_date')->nullable()->change();
      $table->datetime('end_date')->nullable()->change();

      $table->dropColumn('coupon_id');
      $table->dropColumn('billing_info');
      $table->dropColumn('plan_info');
      $table->dropColumn('coupon_info');
      $table->dropColumn('processing_fee_info');
      $table->dropColumn('processing_fee');
      $table->dropColumn('subtotal');
      if (Schema::hasColumn('subscriptions', 'tax_rate')) {
        $table->dropColumn('tax_rate'); // NEW
      }
      $table->dropColumn('total_tax');
      $table->dropColumn('total_amount');
      $table->dropColumn('subscription_level');
      $table->dropColumn('current_period');
      $table->dropColumn('current_period_start_date');
      $table->dropColumn('current_period_end_date');
      $table->dropColumn('next_invoice_date');
      if (Schema::hasColumn('subscriptions', 'next_invoice')) {
        $table->dropColumn('next_invoice'); // NEW
      }
      $table->dropColumn('dr');
      if (Schema::hasColumn('subscriptions', 'dr_subscription_id')) {
        $table->dropColumn('dr_subscription_id'); // NEW
      }
      $table->dropColumn('stop_reason');
      $table->dropColumn('sub_status');
    });

    // 2023_06_02_000000_update_billing_infos_table
    // 2023_03_31_000000_create_billing_infos_table
    $this->info('fix billing_infos_table');
    Schema::dropIfExists('billing_infos');

    // 2023_05_31_000001_update_critical_sections_table
    // 2023_05_31_000000_create_critical_sections_table
    $this->info('fix critical_sections_table');
    Schema::dropIfExists('critical_sections');

    // 2023_05_31_000000_update_countries_table
    // 2023_03_31_000000_update_countries_table
    $this->info('fix countries_table');
    Schema::table('countries', function (Blueprint $table) {
      // 2023_05_31_000000_update_countries_table
      $table->dropColumn('timezone');

      // 2023_03_31_000000_update_countries_table
      $table->renameColumn('code', 'country_code');
      $table->renameColumn('name', 'country');
      $table->dropColumn('currency');
      $table->dropColumn('processing_fee_rate');
      $table->dropColumn('explicit_processing_fee');
      $table->dropTimestamps();
    });

    // 2023_03_31_000000_update_plans_table
    $this->info('fix plans_table');
    Schema::table('plans', function (Blueprint $table) {
      $table->dropUnique(['name']);
      $table->dropIndex(['catagory']);
      $table->dropIndex(['status']);

      $table->dropColumn('price_list');

      $table->string('contract_term')->nullable();
      $table->json('price')->nullable();
      $table->boolean('auto_renew')->default(true);
    });

    // 2023_03_31_000000_update_general_configuration_table
    $this->info('fix general_configuration_table');
    // do nothing

    // 2023_03_31_000000_create_dr_events_table
    $this->info('fix dr_events_table');
    Schema::dropIfExists('dr_events');

    // 2023_03_31_000000_create_coupons_table
    $this->info('fix coupons_table');
    Schema::dropIfExists('coupons');

    $this->info('Fixed migrations between 20230331-20230612 ... done!');

    return self::SUCCESS;
  }
}
