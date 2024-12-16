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
 * @property array|null $display_data
 * @property array $dr
 * @property array|null $meta
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
    'display_data' => 'json',
    'dr' => 'json',
    'meta' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'type',
    'display_data',
    'dr',
    'meta'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
