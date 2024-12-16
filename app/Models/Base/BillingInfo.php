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
 * Class BillingInfo
 * 
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $phone
 * @property string $customer_type
 * @property string|null $organization
 * @property string $email
 * @property array $address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $language
 * @property string $locale
 * @property array|null $meta
 * 
 * @property User $user
 *
 * @package App\Models\Base
 */
class BillingInfo extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'billing_infos';

  protected $casts = [
    'user_id' => 'int',
    'address' => 'json',
    'meta' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'first_name',
    'last_name',
    'phone',
    'customer_type',
    'organization',
    'email',
    'address',
    'language',
    'locale',
    'meta'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
