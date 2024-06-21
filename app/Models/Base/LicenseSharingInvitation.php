<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\LicenseSharing;
use App\Models\Product;
use App\Models\TraitModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LicenseSharingInvitation
 * 
 * @property int $id
 * @property int $license_sharing_id
 * @property string $product_name
 * @property int $subscription_level
 * @property int $owner_id
 * @property string $owner_name
 * @property string $owner_email
 * @property int $guest_id
 * @property string $guest_name
 * @property string $guest_email
 * @property Carbon $expires_at
 * @property string $status
 * @property array|null $logs
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $guest
 * @property LicenseSharing $license_sharing
 * @property User $owner
 * @property Product $product
 *
 * @package App\Models\Base
 */
class LicenseSharingInvitation extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'license_sharing_invitations';

  protected $casts = [
    'license_sharing_id' => 'int',
    'subscription_level' => 'int',
    'owner_id' => 'int',
    'guest_id' => 'int',
    'expires_at' => 'datetime',
    'logs' => 'json'
  ];

  protected $fillable = [
    'license_sharing_id',
    'product_name',
    'subscription_level',
    'owner_id',
    'owner_name',
    'owner_email',
    'guest_id',
    'guest_name',
    'guest_email',
    'expires_at',
    'status',
    'logs'
  ];

  public function guest()
  {
    return $this->belongsTo(User::class, 'guest_id');
  }

  public function license_sharing()
  {
    return $this->belongsTo(LicenseSharing::class);
  }

  public function owner()
  {
    return $this->belongsTo(User::class, 'owner_id');
  }

  public function product()
  {
    return $this->belongsTo(Product::class, 'product_name', 'name');
  }
}
