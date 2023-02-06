<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TraitModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PaymentMethod
 * 
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property array|null $credit_card
 * @property string $provider_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models\Base
 */
class PaymentMethod extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'payment_methods';

  protected $casts = [
    'user_id' => 'int',
    'credit_card' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'type',
    'credit_card',
    'provider_id'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
