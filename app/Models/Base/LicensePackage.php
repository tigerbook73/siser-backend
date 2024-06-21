<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LicensePackage
 * 
 * @property int $id
 * @property string $type
 * @property string $name
 * @property array $price_table
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class LicensePackage extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'license_packages';

  protected $casts = [
    'price_table' => 'json'
  ];

  protected $fillable = [
    'type',
    'name',
    'price_table',
    'status'
  ];
}
