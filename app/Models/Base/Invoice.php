<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Subscription;
use App\Models\TraitModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Invoice
 * 
 * @property int $id
 * @property int $user_id
 * @property int $subscription_id
 * @property int $period
 * @property Carbon $period_start_date
 * @property Carbon $period_end_date
 * @property string $currency
 * @property array $plan_info
 * @property array|null $coupon_info
 * @property array $processing_fee_info
 * @property float $subtotal
 * @property float $total_tax
 * @property float $total_amount
 * @property Carbon $invoice_date
 * @property string|null $pdf_file
 * @property array $dr
 * @property string|null $dr_invoice_id
 * @property string|null $dr_order_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array|null $status_transitions
 * 
 * @property Subscription $subscription
 * @property User $user
 *
 * @package App\Models\Base
 */
class Invoice extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'invoices';

  protected $casts = [
    'user_id' => 'int',
    'subscription_id' => 'int',
    'period' => 'int',
    'period_start_date' => 'datetime',
    'period_end_date' => 'datetime',
    'plan_info' => 'json',
    'coupon_info' => 'json',
    'processing_fee_info' => 'json',
    'subtotal' => 'float',
    'total_tax' => 'float',
    'total_amount' => 'float',
    'invoice_date' => 'datetime',
    'dr' => 'json',
    'status_transitions' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'subscription_id',
    'period',
    'period_start_date',
    'period_end_date',
    'currency',
    'plan_info',
    'coupon_info',
    'processing_fee_info',
    'subtotal',
    'total_tax',
    'total_amount',
    'invoice_date',
    'pdf_file',
    'dr',
    'dr_invoice_id',
    'dr_order_id',
    'status',
    'status_transitions'
  ];

  public function subscription()
  {
    return $this->belongsTo(Subscription::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
