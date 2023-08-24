<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\BillingInfo;
use App\Models\Invoice;
use App\Models\LdsLicense;
use App\Models\Machine;
use App\Models\PaymentMethod;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\TaxId;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $cognito_id
 * @property string|null $given_name
 * @property string|null $family_name
 * @property string $full_name
 * @property string|null $phone_number
 * @property string|null $country_code
 * @property string|null $language_code
 * @property int|null $subscription_level
 * @property int|null $license_count
 * @property array|null $roles
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array|null $dr
 * @property string $type
 * @property string|null $timezone
 * 
 * @property BillingInfo $billing_info
 * @property Collection|Invoice[] $invoices
 * @property LdsLicense $lds_license
 * @property Collection|Machine[] $machines
 * @property PaymentMethod $payment_method
 * @property Collection|Refund[] $refunds
 * @property Collection|Subscription[] $subscriptions
 * @property Collection|TaxId[] $tax_ids
 *
 * @package App\Models\Base
 */
class User extends \Illuminate\Foundation\Auth\User
{
  use HasFactory;
  protected $table = 'users';

  protected $casts = [
    'email_verified_at' => 'datetime',
    'subscription_level' => 'int',
    'license_count' => 'int',
    'roles' => 'json',
    'dr' => 'json'
  ];

  protected $fillable = [
    'name',
    'email',
    'password',
    'cognito_id',
    'given_name',
    'family_name',
    'full_name',
    'phone_number',
    'country_code',
    'language_code',
    'subscription_level',
    'license_count',
    'roles',
    'dr',
    'type',
    'timezone'
  ];

  public function billing_info()
  {
    return $this->hasOne(BillingInfo::class);
  }

  public function invoices()
  {
    return $this->hasMany(Invoice::class);
  }

  public function lds_license()
  {
    return $this->hasOne(LdsLicense::class);
  }

  public function machines()
  {
    return $this->hasMany(Machine::class);
  }

  public function payment_method()
  {
    return $this->hasOne(PaymentMethod::class);
  }

  public function refunds()
  {
    return $this->hasMany(Refund::class);
  }

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class);
  }

  public function tax_ids()
  {
    return $this->hasMany(TaxId::class);
  }
}
