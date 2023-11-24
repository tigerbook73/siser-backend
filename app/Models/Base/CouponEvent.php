<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Coupon;
use App\Models\TraitModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponEvent
 * 
 * @property int $id
 * @property string $name
 * 
 * @property Collection|Coupon[] $coupons
 *
 * @package App\Models\Base
 */
class CouponEvent extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'coupon_events';
  public $timestamps = false;

  protected $fillable = [
    'name'
  ];

  public function coupons()
  {
    return $this->hasMany(Coupon::class, 'coupon_event', 'name');
  }
}
