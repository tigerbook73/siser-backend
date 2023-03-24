<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\SoftwarePackageLatest;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SoftwarePackage
 * 
 * @property int $id
 * @property string $name
 * @property string $platform
 * @property string $version
 * @property string|null $description
 * @property string $version_type
 * @property Carbon $released_date
 * @property string|null $release_notes
 * @property string $filename
 * @property string $url
 * @property string|null $file_hash
 * @property bool $force_update
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array|null $release_notes_text
 * @property string|null $status
 * 
 * @property SoftwarePackageLatest $software_package_latest
 *
 * @package App\Models\Base
 */
class SoftwarePackage extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'software_packages';

  protected $casts = [
    'released_date' => 'date',
    'force_update' => 'bool',
    'release_notes_text' => 'json'
  ];

  protected $fillable = [
    'name',
    'platform',
    'version',
    'description',
    'version_type',
    'released_date',
    'release_notes',
    'filename',
    'url',
    'file_hash',
    'force_update',
    'release_notes_text',
    'status'
  ];

  public function software_package_latest()
  {
    return $this->hasOne(SoftwarePackageLatest::class);
  }
}
