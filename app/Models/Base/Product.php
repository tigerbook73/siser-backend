<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Coupon;
use App\Models\LicenseSharing;
use App\Models\LicenseSharingInvitation;
use App\Models\Plan;
use App\Models\TraitModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property int $id
 * @property string $name
 * 
 * @property Collection|Coupon[] $coupons
 * @property Collection|LicenseSharingInvitation[] $license_sharing_invitations
 * @property Collection|LicenseSharing[] $license_sharings
 * @property Collection|Plan[] $plans
 *
 * @package App\Models\Base
 */
class Product extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'products';
  public $timestamps = false;

  protected $fillable = [
    'name'
  ];

  public function coupons()
  {
    return $this->hasMany(Coupon::class, 'product_name', 'name');
  }

  public function license_sharing_invitations()
  {
    return $this->hasMany(LicenseSharingInvitation::class, 'product_name', 'name');
  }

  public function license_sharings()
  {
    return $this->hasMany(LicenseSharing::class, 'product_name', 'name');
  }

  public function plans()
  {
    return $this->hasMany(Plan::class, 'product_name', 'name');
  }
}
