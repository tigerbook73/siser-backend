<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\SoftwarePackage;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SoftwarePackageLatest
 * 
 * @property int $id
 * @property string $name
 * @property string $platform
 * @property string $version_type
 * @property int $software_package_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SoftwarePackage $software_package
 *
 * @package App\Models\Base
 */
class SoftwarePackageLatest extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'software_package_latests';

  protected $casts = [
    'software_package_id' => 'int'
  ];

  protected $fillable = [
    'name',
    'platform',
    'version_type',
    'software_package_id'
  ];

  public function software_package()
  {
    return $this->belongsTo(SoftwarePackage::class);
  }
}
