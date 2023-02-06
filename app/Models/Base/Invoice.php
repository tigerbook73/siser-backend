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
 * @property string $currency
 * @property array $plan
 * @property array|null $coupon
 * @property array $processing_fee
 * @property float $amount
 * @property float $tax
 * @property float $total_amount
 * @property Carbon $invoice_date
 * @property string|null $pdf_file
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
    'plan' => 'json',
    'coupon' => 'json',
    'processing_fee' => 'json',
    'amount' => 'float',
    'tax' => 'float',
    'total_amount' => 'float'
  ];

  protected $dates = [
    'invoice_date'
  ];

  protected $fillable = [
    'user_id',
    'subscription_id',
    'period',
    'currency',
    'plan',
    'coupon',
    'processing_fee',
    'amount',
    'tax',
    'total_amount',
    'invoice_date',
    'pdf_file',
    'status'
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
