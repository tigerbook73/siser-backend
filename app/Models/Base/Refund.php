<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\TraitModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Refund
 * 
 * @property int $id
 * @property int $user_id
 * @property int $subscription_id
 * @property int $invoice_id
 * @property string $currency
 * @property float $amount
 * @property string|null $reason
 * @property array|null $payment_method_info
 * @property array|null $dr
 * @property string $dr_refund_id
 * @property string $status
 * @property array|null $status_transitions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Invoice $invoice
 * @property Subscription $subscription
 * @property User $user
 *
 * @package App\Models\Base
 */
class Refund extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'refunds';

  protected $casts = [
    'user_id' => 'int',
    'subscription_id' => 'int',
    'invoice_id' => 'int',
    'amount' => 'float',
    'payment_method_info' => 'json',
    'dr' => 'json',
    'status_transitions' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'subscription_id',
    'invoice_id',
    'currency',
    'amount',
    'reason',
    'payment_method_info',
    'dr',
    'dr_refund_id',
    'status',
    'status_transitions'
  ];

  public function invoice()
  {
    return $this->belongsTo(Invoice::class);
  }

  public function subscription()
  {
    return $this->belongsTo(Subscription::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
