<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\LdsInstance;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LdsPool
 * 
 * @property int $id
 * @property int $user_id
 * @property int $subscription_level
 * @property int $license_count
 * @property int $license_free
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|LdsInstance[] $lds_instances
 *
 * @package App\Models\Base
 */
class LdsPool extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'lds_pools';

  protected $casts = [
    'user_id' => 'int',
    'subscription_level' => 'int',
    'license_count' => 'int',
    'license_free' => 'int'
  ];

  protected $fillable = [
    'user_id',
    'subscription_level',
    'license_count',
    'license_free'
  ];

  public function lds_instances()
  {
    return $this->hasMany(LdsInstance::class);
  }
}
