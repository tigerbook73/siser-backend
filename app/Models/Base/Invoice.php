<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Refund;
use App\Models\Subscription;
use App\Models\TraitModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Invoice
 * 
 * @property int $id
 * @property int $user_id
 * @property int $subscription_id
 * @property int $period
 * @property Carbon|null $period_start_date
 * @property Carbon|null $period_end_date
 * @property string $currency
 * @property array|null $billing_info
 * @property array|null $tax_id_info
 * @property array $plan_info
 * @property array|null $coupon_info
 * @property float $subtotal
 * @property float $total_tax
 * @property float $total_amount
 * @property float $total_refunded
 * @property Carbon|null $invoice_date
 * @property string|null $pdf_file
 * @property array|null $credit_memos
 * @property array $dr
 * @property string|null $dr_invoice_id
 * @property string|null $dr_order_id
 * @property string $status
 * @property string $sub_status
 * @property string $dispute_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array|null $status_transitions
 * @property array|null $dispute_status_transitions
 * @property array|null $payment_method_info
 * 
 * @property Subscription $subscription
 * @property User $user
 * @property Collection|Refund[] $refunds
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
    'billing_info' => 'json',
    'tax_id_info' => 'json',
    'plan_info' => 'json',
    'coupon_info' => 'json',
    'subtotal' => 'float',
    'total_tax' => 'float',
    'total_amount' => 'float',
    'total_refunded' => 'float',
    'invoice_date' => 'datetime',
    'credit_memos' => 'json',
    'dr' => 'json',
    'status_transitions' => 'json',
    'dispute_status_transitions' => 'json',
    'payment_method_info' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'subscription_id',
    'period',
    'period_start_date',
    'period_end_date',
    'currency',
    'billing_info',
    'tax_id_info',
    'plan_info',
    'coupon_info',
    'subtotal',
    'total_tax',
    'total_amount',
    'total_refunded',
    'invoice_date',
    'pdf_file',
    'credit_memos',
    'dr',
    'dr_invoice_id',
    'dr_order_id',
    'status',
    'sub_status',
    'dispute_status',
    'status_transitions',
    'dispute_status_transitions',
    'payment_method_info'
  ];

  public function subscription()
  {
    return $this->belongsTo(Subscription::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function refunds()
  {
    return $this->hasMany(Refund::class);
  }
}
