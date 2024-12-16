<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\LicensePackage;
use App\Models\Plan;
use App\Models\Product;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LicensePlan
 * 
 * @property int $id
 * @property string $product_name
 * @property int $license_package_id
 * @property int $plan_id
 * @property string $interval
 * @property int $interval_count
 * @property array|null $details
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property LicensePackage $license_package
 * @property Plan $plan
 * @property Product $product
 *
 * @package App\Models\Base
 */
class LicensePlan extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'license_plans';

  protected $casts = [
    'license_package_id' => 'int',
    'plan_id' => 'int',
    'interval_count' => 'int',
    'details' => 'json'
  ];

  protected $fillable = [
    'product_name',
    'license_package_id',
    'plan_id',
    'interval',
    'interval_count',
    'details'
  ];

  public function license_package()
  {
    return $this->belongsTo(LicensePackage::class);
  }

  public function plan()
  {
    return $this->belongsTo(Plan::class);
  }

  public function product()
  {
    return $this->belongsTo(Product::class, 'product_name', 'name');
  }
}
